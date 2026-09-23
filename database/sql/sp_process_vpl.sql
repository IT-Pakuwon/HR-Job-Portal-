-- Lives on connection `pgsql5` / database `db_das` (production). Not deployed by any
-- migration/trigger/pg_cron job today; every doctype (VPR/VPT/VPU/VPS) is
-- invoked manually via `CALL sp_process_vpl(doctype, docid, cpny_id, activity, user)`.
-- Keep this file in sync by hand after any live ALTER/CREATE OR REPLACE against
-- pgsql5 until proper migrations are set up for it.
--
-- 2026-09-03: re-synced from the LIVE db_das definition via pg_get_functiondef —
-- the previous version-controlled copy (2643 lines) was stale relative to
-- production (3004 lines) and was missing the usagetype-aware Return handling
-- and the department('CUSTOMERSERVICE')-based posting-period logic that had
-- already been deployed directly to prod by someone else. On top of that live
-- copy, this sync ALSO fixes a gap in that same department-based posting-date
-- work: VPT/VPU/VPS's v_perpost/v_year/v_month (the ms_vpl_product_bal period
-- bucket) had already been switched to use CURRENT_TIMESTAMP (non-CUSTOMERSERVICE
-- / "Promotion") vs the document's own date (CUSTOMERSERVICE / "Loyalty"), but
-- the tr_vpl_ledger row's own refdate/postdate columns were left reading the raw
-- document date field unconditionally in all 4 ledger INSERTs (2 in VPT, 1 in
-- VPU, 1 in VPS) — so the ledger and the balance table disagreed on posting date.
-- Added a `v_post_date` variable set identically to how v_perpost/etc. are
-- already decided in each branch, and pointed those 4 INSERTs at it instead.
-- Net posting-date rule per doctype, confirmed with the user:
--   Receive             -> receive_date (unchanged)
--   Transfer/ReturnTf   -> completed-approval time (CURRENT_TIMESTAMP at SP run)
--   Usage/Return-Usage at Promotion (dept <> CUSTOMERSERVICE) -> completed-approval time
--   Usage/Return-Usage at Loyalty (dept = CUSTOMERSERVICE)    -> usage_date (unchanged)
--   Settlement at Promotion (dept <> CUSTOMERSERVICE) -> completed-approval time
--   Settlement at Loyalty (dept = CUSTOMERSERVICE)    -> settlement_date (unchanged)
-- Verified by a dry-run CREATE OR REPLACE PROCEDURE wrapped in BEGIN/ROLLBACK
-- against live db_das (compiled clean, nothing committed) before real deploy.
CREATE OR REPLACE PROCEDURE public.sp_process_vpl(IN p_doctype character varying, IN p_docid character varying, IN p_cpny_id character varying, IN p_activity character varying, IN p_user character varying)
 LANGUAGE plpgsql
AS $procedure$

DECLARE
    v_cpnyid    VARCHAR(10);
    v_perpost   VARCHAR(6);
    v_year      VARCHAR(4);
    v_month     INTEGER;
    v_activity  VARCHAR(20);
    v_qty       INTEGER;
    v_row_count INTEGER := 0;
    v_usagetype VARCHAR(20);
		v_department VARCHAR(25);
		v_usage_date DATE;
		v_settlement_date DATE;
		v_post_date TIMESTAMP;

    r_detail RECORD;
BEGIN

    /*
    ================================================================
    VALIDASI PARAMETER UMUM
    Berlaku untuk seluruh doctype.
    ================================================================
    */

    IF NULLIF(TRIM(COALESCE(p_doctype, '')), '') IS NULL THEN
        RAISE EXCEPTION
            'Doctype tidak boleh kosong.';
    END IF;

    IF NULLIF(TRIM(COALESCE(p_docid, '')), '') IS NULL THEN
        RAISE EXCEPTION
            'Document ID tidak boleh kosong.';
    END IF;

    IF NULLIF(TRIM(COALESCE(p_cpny_id, '')), '') IS NULL THEN
        RAISE EXCEPTION
            'Company ID tidak boleh kosong.';
    END IF;

    IF NULLIF(TRIM(COALESCE(p_activity, '')), '') IS NULL THEN
        RAISE EXCEPTION
            'Activity tidak boleh kosong.';
    END IF;

    IF NULLIF(TRIM(COALESCE(p_user, '')), '') IS NULL THEN
        RAISE EXCEPTION
            'User tidak boleh kosong.';
    END IF;

    /*
    ================================================================
    NORMALISASI ACTIVITY UMUM
    ================================================================
    */

    v_activity :=
        CASE UPPER(TRIM(p_activity))
            WHEN 'SUBMIT' THEN 'Submit'
            WHEN 'REJECT' THEN 'Reject'
            WHEN 'REVISE' THEN 'Revise'
            ELSE NULL
        END;

    IF v_activity IS NULL THEN
        RAISE EXCEPTION
            'Activity % tidak valid. Gunakan Submit, Reject, atau Revise.',
            p_activity;
    END IF;

    /*
    ================================================================
    DOCTYPE VPR
    PENERIMAAN VOUCHER
    ================================================================
    */

    IF UPPER(TRIM(p_doctype)) = 'VPR' THEN

        v_row_count := 0;

        /*
        ================================================================
        AMBIL HEADER PENERIMAAN
        ================================================================
        */

			SELECT
					h.cpnyid,
					TO_CHAR(h.receive_date, 'YYYYMM'),
					TO_CHAR(h.receive_date, 'YYYY'),
					EXTRACT(MONTH FROM h.receive_date)::INTEGER
			INTO
					v_cpnyid,
					v_perpost,
					v_year,
					v_month
			FROM tr_vpl_receive h
			WHERE h.receive_id = p_docid
			FOR UPDATE;

			IF NOT FOUND THEN
					RAISE EXCEPTION
							'Dokumen penerimaan voucher % tidak ditemukan.',
							p_docid;
			END IF;

            IF v_perpost IS NULL THEN
                RAISE EXCEPTION
                    'Receive date dokumen % tidak boleh kosong.',
                    p_docid;
            END IF;

			IF v_cpnyid IS DISTINCT FROM p_cpny_id THEN
					RAISE EXCEPTION
							'Company dokumen tidak sesuai. Company dokumen: %, parameter: %.',
							v_cpnyid,
							p_cpny_id;
			END IF;

			/*
			================================================================
			LOOP SETIAP LINE DETAIL

			Kombinasi product, expired date, dan warehouse boleh sama.
			Setiap line tetap diproses secara terpisah berdasarkan linenbr.
			================================================================
			*/

			FOR r_detail IN
					SELECT
							d.id,
							d.receive_id,
							d.linenbr,
							d.product_id,
							d.expired_date,
							d.whs_id,
							COALESCE(d.qty_receive, 0) AS qty_receive,
							h.receive_date
					FROM tr_vpl_receive_detail d
					INNER JOIN tr_vpl_receive h
							ON h.receive_id = d.receive_id
					WHERE d.receive_id = p_docid
					ORDER BY d.linenbr, d.id
					FOR UPDATE OF d
			LOOP
					v_row_count := v_row_count + 1;

					/*
					============================================================
					VALIDASI DETAIL
					============================================================
					*/

					IF r_detail.linenbr IS NULL THEN
							RAISE EXCEPTION
									'Line number kosong pada detail ID %, dokumen %.',
									r_detail.id,
									p_docid;
					END IF;

					IF NULLIF(TRIM(r_detail.product_id), '') IS NULL THEN
							RAISE EXCEPTION
									'Product ID kosong pada dokumen %, line %.',
									p_docid,
									r_detail.linenbr;
					END IF;

					IF NULLIF(TRIM(r_detail.whs_id), '') IS NULL THEN
							RAISE EXCEPTION
									'Warehouse ID kosong pada dokumen %, line %.',
									p_docid,
									r_detail.linenbr;
					END IF;

					IF r_detail.qty_receive <= 0 THEN
							RAISE EXCEPTION
									'Qty receive harus lebih besar dari 0. Dokumen %, line %, qty %.',
									p_docid,
									r_detail.linenbr,
									r_detail.qty_receive;
					END IF;

            /*
            ============================================================
            VALIDASI STATUS MASTER PRODUCT & WAREHOUSE (added 2026-08-10, SPM-02 fix)

            Hanya diblokir jika master DITEMUKAN dan berstatus non-aktif.
            Baris master yang tidak ditemukan sama sekali tidak diblokir
            di sini (di luar scope perbaikan ini).
            ============================================================
            */

            IF EXISTS (
                SELECT 1 FROM ms_vpl_product p
                WHERE p.product_id = r_detail.product_id
                  AND p.cpnyid = p_cpny_id
                  AND COALESCE(p.status, '') <> 'A'
            ) THEN
                RAISE EXCEPTION
                    'Product % (company %) berstatus tidak aktif. Dokumen %, line %.',
                    r_detail.product_id, p_cpny_id, p_docid, r_detail.linenbr;
            END IF;

            IF EXISTS (
                SELECT 1 FROM ms_vpl_warehouse w
                WHERE w.whs_id = r_detail.whs_id
                  AND w.cpnyid = p_cpny_id
                  AND COALESCE(w.status, '') <> 'A'
            ) THEN
                RAISE EXCEPTION
                    'Warehouse % (company %) berstatus tidak aktif. Dokumen %, line %.',
                    r_detail.whs_id, p_cpny_id, p_docid, r_detail.linenbr;
            END IF;

					/*
					============================================================
					CEK NET LEDGER PER LINE

					Penguncian transaksi penerimaan berdasarkan:
					- refnbr
					- cpnyid
					- linenbr
					- product_id
					- expired_date
					- whs_id

					Linenbr wajib digunakan karena product, expired date,
					dan warehouse dapat sama pada beberapa line.
					============================================================
					*/

					SELECT COALESCE(SUM(l.qty), 0)
					INTO v_qty
					FROM tr_vpl_ledger l
					WHERE l.refnbr = p_docid
						AND l.cpnyid = p_cpny_id
						AND l.linenbr = r_detail.linenbr
						AND l.product_id = r_detail.product_id
						AND l.expired_date IS NOT DISTINCT FROM r_detail.expired_date
						AND l.whs_id = r_detail.whs_id
						AND l.status = 'A';

					/*
					============================================================
					TENTUKAN QTY LEDGER
					============================================================
					*/

					IF v_activity = 'Submit' THEN

							IF v_qty <> 0 THEN
									RAISE EXCEPTION
											'Line sudah pernah Submit dan belum dibalikkan. '
											'Dokumen %, line %, product %, expired date %, '
											'warehouse %, net ledger %.',
											p_docid,
											r_detail.linenbr,
											r_detail.product_id,
											r_detail.expired_date,
											r_detail.whs_id,
											v_qty;
							END IF;

							v_qty := r_detail.qty_receive;

					ELSE

							IF v_qty <= 0 THEN
									RAISE EXCEPTION
											'Line tidak dapat di-%. Penerimaan sudah tidak aktif. '
											'Dokumen %, line %, product %, expired date %, warehouse %.',
											LOWER(v_activity),
											p_docid,
											r_detail.linenbr,
											r_detail.product_id,
											r_detail.expired_date,
											r_detail.whs_id;
							END IF;

							/*
							Reject/Revise membalikkan qty aktif line tersebut.
							*/

							v_qty := v_qty * -1;

					END IF;

					/*
					============================================================
					ACTIVITY 1
					MEMBUAT TR_VPL_LEDGER
					============================================================
					*/

					INSERT INTO tr_vpl_ledger
					(
							refnbr,
							refdate,
							reference_refnbr,
							cpnyid,
							postdate,
							perpost,
							linenbr,
							product_id,
							expired_date,
							whs_id,
							purpose_id,
							transaction_source,
							transaction_activity,
							qty,
							status,
							created_user,
							created_at
					)
					VALUES
					(
							p_docid,
							r_detail.receive_date,
							NULL,
							p_cpny_id,
							r_detail.receive_date,
							v_perpost,
							r_detail.linenbr,
							r_detail.product_id,
							r_detail.expired_date,
							r_detail.whs_id,
							NULL,
							'Receive',
							v_activity,
							v_qty,
							'A',
							p_user,
							CURRENT_TIMESTAMP
					);

					/*
					============================================================
					ACTIVITY 2
					UPDATE MONTHLY STOCK BALANCE

					Balance tetap digabung berdasarkan:
					- year
					- cpnyid
					- product_id
					- expired_date
					- whs_id

					Tidak menggunakan linenbr karena tabel balance merupakan
					total stock per product, expired date, dan warehouse.
					============================================================
					*/

					UPDATE ms_vpl_product_bal
					SET
							perpost = v_perpost,

							period01in = COALESCE(period01in, 0)
									+ CASE
											WHEN v_month = 1 AND v_activity = 'Submit'
											THEN ABS(v_qty)
											ELSE 0
										END,
							period01out = COALESCE(period01out, 0)
									+ CASE
											WHEN v_month = 1
											 AND v_activity IN ('Reject', 'Revise')
											THEN ABS(v_qty)
											ELSE 0
										END,

							period02in = COALESCE(period02in, 0)
									+ CASE
											WHEN v_month = 2 AND v_activity = 'Submit'
											THEN ABS(v_qty)
											ELSE 0
										END,
							period02out = COALESCE(period02out, 0)
									+ CASE
											WHEN v_month = 2
											 AND v_activity IN ('Reject', 'Revise')
											THEN ABS(v_qty)
											ELSE 0
										END,

							period03in = COALESCE(period03in, 0)
									+ CASE
											WHEN v_month = 3 AND v_activity = 'Submit'
											THEN ABS(v_qty)
											ELSE 0
										END,
							period03out = COALESCE(period03out, 0)
									+ CASE
											WHEN v_month = 3
											 AND v_activity IN ('Reject', 'Revise')
											THEN ABS(v_qty)
											ELSE 0
										END,

							period04in = COALESCE(period04in, 0)
									+ CASE
											WHEN v_month = 4 AND v_activity = 'Submit'
											THEN ABS(v_qty)
											ELSE 0
										END,
							period04out = COALESCE(period04out, 0)
									+ CASE
											WHEN v_month = 4
											 AND v_activity IN ('Reject', 'Revise')
											THEN ABS(v_qty)
											ELSE 0
										END,

							period05in = COALESCE(period05in, 0)
									+ CASE
											WHEN v_month = 5 AND v_activity = 'Submit'
											THEN ABS(v_qty)
											ELSE 0
										END,
							period05out = COALESCE(period05out, 0)
									+ CASE
											WHEN v_month = 5
											 AND v_activity IN ('Reject', 'Revise')
											THEN ABS(v_qty)
											ELSE 0
										END,

							period06in = COALESCE(period06in, 0)
									+ CASE
											WHEN v_month = 6 AND v_activity = 'Submit'
											THEN ABS(v_qty)
											ELSE 0
										END,
							period06out = COALESCE(period06out, 0)
									+ CASE
											WHEN v_month = 6
											 AND v_activity IN ('Reject', 'Revise')
											THEN ABS(v_qty)
											ELSE 0
										END,

							period07in = COALESCE(period07in, 0)
									+ CASE
											WHEN v_month = 7 AND v_activity = 'Submit'
											THEN ABS(v_qty)
											ELSE 0
										END,
							period07out = COALESCE(period07out, 0)
									+ CASE
											WHEN v_month = 7
											 AND v_activity IN ('Reject', 'Revise')
											THEN ABS(v_qty)
											ELSE 0
										END,

							period08in = COALESCE(period08in, 0)
									+ CASE
											WHEN v_month = 8 AND v_activity = 'Submit'
											THEN ABS(v_qty)
											ELSE 0
										END,
							period08out = COALESCE(period08out, 0)
									+ CASE
											WHEN v_month = 8
											 AND v_activity IN ('Reject', 'Revise')
											THEN ABS(v_qty)
											ELSE 0
										END,

							period09in = COALESCE(period09in, 0)
									+ CASE
											WHEN v_month = 9 AND v_activity = 'Submit'
											THEN ABS(v_qty)
											ELSE 0
										END,
							period09out = COALESCE(period09out, 0)
									+ CASE
											WHEN v_month = 9
											 AND v_activity IN ('Reject', 'Revise')
											THEN ABS(v_qty)
											ELSE 0
										END,

							period10in = COALESCE(period10in, 0)
									+ CASE
											WHEN v_month = 10 AND v_activity = 'Submit'
											THEN ABS(v_qty)
											ELSE 0
										END,
							period10out = COALESCE(period10out, 0)
									+ CASE
											WHEN v_month = 10
											 AND v_activity IN ('Reject', 'Revise')
											THEN ABS(v_qty)
											ELSE 0
										END,

							period11in = COALESCE(period11in, 0)
									+ CASE
											WHEN v_month = 11 AND v_activity = 'Submit'
											THEN ABS(v_qty)
											ELSE 0
										END,
							period11out = COALESCE(period11out, 0)
									+ CASE
											WHEN v_month = 11
											 AND v_activity IN ('Reject', 'Revise')
											THEN ABS(v_qty)
											ELSE 0
										END,

							period12in = COALESCE(period12in, 0)
									+ CASE
											WHEN v_month = 12 AND v_activity = 'Submit'
											THEN ABS(v_qty)
											ELSE 0
										END,
							period12out = COALESCE(period12out, 0)
									+ CASE
											WHEN v_month = 12
											 AND v_activity IN ('Reject', 'Revise')
											THEN ABS(v_qty)
											ELSE 0
										END,

							updated_user = p_user,
							updated_at = CURRENT_TIMESTAMP
					WHERE year = v_year
						AND cpnyid = p_cpny_id
						AND product_id = r_detail.product_id
						AND expired_date IS NOT DISTINCT FROM r_detail.expired_date
						AND whs_id = r_detail.whs_id;

					IF NOT FOUND THEN
							INSERT INTO ms_vpl_product_bal
							(
									year,
									perpost,
									product_id,
									expired_date,
									cpnyid,
									whs_id,
									begqty,
									period01in,
									period01out,
									period02in,
									period02out,
									period03in,
									period03out,
									period04in,
									period04out,
									period05in,
									period05out,
									period06in,
									period06out,
									period07in,
									period07out,
									period08in,
									period08out,
									period09in,
									period09out,
									period10in,
									period10out,
									period11in,
									period11out,
									period12in,
									period12out,
									status,
									created_user,
									created_at
							)
							VALUES
							(
									v_year,
									v_perpost,
									r_detail.product_id,
									r_detail.expired_date,
									p_cpny_id,
									r_detail.whs_id,
									0,

									CASE WHEN v_month = 1  AND v_activity = 'Submit'
											 THEN ABS(v_qty) ELSE 0 END,
									CASE WHEN v_month = 1  AND v_activity IN ('Reject', 'Revise')
											 THEN ABS(v_qty) ELSE 0 END,

									CASE WHEN v_month = 2  AND v_activity = 'Submit'
											 THEN ABS(v_qty) ELSE 0 END,
									CASE WHEN v_month = 2  AND v_activity IN ('Reject', 'Revise')
											 THEN ABS(v_qty) ELSE 0 END,

									CASE WHEN v_month = 3  AND v_activity = 'Submit'
											 THEN ABS(v_qty) ELSE 0 END,
									CASE WHEN v_month = 3  AND v_activity IN ('Reject', 'Revise')
											 THEN ABS(v_qty) ELSE 0 END,

									CASE WHEN v_month = 4  AND v_activity = 'Submit'
											 THEN ABS(v_qty) ELSE 0 END,
									CASE WHEN v_month = 4  AND v_activity IN ('Reject', 'Revise')
											 THEN ABS(v_qty) ELSE 0 END,

									CASE WHEN v_month = 5  AND v_activity = 'Submit'
											 THEN ABS(v_qty) ELSE 0 END,
									CASE WHEN v_month = 5  AND v_activity IN ('Reject', 'Revise')
											 THEN ABS(v_qty) ELSE 0 END,

									CASE WHEN v_month = 6  AND v_activity = 'Submit'
											 THEN ABS(v_qty) ELSE 0 END,
									CASE WHEN v_month = 6  AND v_activity IN ('Reject', 'Revise')
											 THEN ABS(v_qty) ELSE 0 END,

									CASE WHEN v_month = 7  AND v_activity = 'Submit'
											 THEN ABS(v_qty) ELSE 0 END,
									CASE WHEN v_month = 7  AND v_activity IN ('Reject', 'Revise')
											 THEN ABS(v_qty) ELSE 0 END,

									CASE WHEN v_month = 8  AND v_activity = 'Submit'
											 THEN ABS(v_qty) ELSE 0 END,
									CASE WHEN v_month = 8  AND v_activity IN ('Reject', 'Revise')
											 THEN ABS(v_qty) ELSE 0 END,

									CASE WHEN v_month = 9  AND v_activity = 'Submit'
											 THEN ABS(v_qty) ELSE 0 END,
									CASE WHEN v_month = 9  AND v_activity IN ('Reject', 'Revise')
											 THEN ABS(v_qty) ELSE 0 END,

									CASE WHEN v_month = 10 AND v_activity = 'Submit'
											 THEN ABS(v_qty) ELSE 0 END,
									CASE WHEN v_month = 10 AND v_activity IN ('Reject', 'Revise')
											 THEN ABS(v_qty) ELSE 0 END,

									CASE WHEN v_month = 11 AND v_activity = 'Submit'
											 THEN ABS(v_qty) ELSE 0 END,
									CASE WHEN v_month = 11 AND v_activity IN ('Reject', 'Revise')
											 THEN ABS(v_qty) ELSE 0 END,

									CASE WHEN v_month = 12 AND v_activity = 'Submit'
											 THEN ABS(v_qty) ELSE 0 END,
									CASE WHEN v_month = 12 AND v_activity IN ('Reject', 'Revise')
											 THEN ABS(v_qty) ELSE 0 END,

									'A',
									p_user,
									CURRENT_TIMESTAMP
							);
					END IF;

					/*
					============================================================
					ACTIVITY 3
					UPDATE STOCK QTY

					Stock tetap digabung berdasarkan product, expired date,
					company, dan warehouse.

					Karena proses dilakukan per line:
					line 1 +100
					line 2 +50
					hasil stock +150
					============================================================
					*/

					UPDATE ms_vpl_product_detail
					SET
							qty_available = COALESCE(qty_available, 0) + v_qty,
							updated_user = p_user,
							updated_at = CURRENT_TIMESTAMP
					WHERE cpnyid = p_cpny_id
						AND product_id = r_detail.product_id
						AND expired_date IS NOT DISTINCT FROM r_detail.expired_date
						AND whs_id = r_detail.whs_id;

					IF NOT FOUND THEN
							IF v_activity <> 'Submit' THEN
									RAISE EXCEPTION
											'Stock product tidak ditemukan untuk proses %. '
											'Dokumen %, line %, product %, expired date %, warehouse %.',
											v_activity,
											p_docid,
											r_detail.linenbr,
											r_detail.product_id,
											r_detail.expired_date,
											r_detail.whs_id;
							END IF;

							INSERT INTO ms_vpl_product_detail
							(
									product_id,
									expired_date,
									cpnyid,
									whs_id,
									qty_available,
									target_date,
									status,
									created_user,
									created_at
							)
							VALUES
							(
									r_detail.product_id,
									r_detail.expired_date,
									p_cpny_id,
									r_detail.whs_id,
									v_qty,
									NULL,
									'A',
									p_user,
									CURRENT_TIMESTAMP
							);
					END IF;

					/*
					============================================================
					VALIDASI STOCK TIDAK MINUS
					============================================================
					*/

					IF EXISTS
					(
							SELECT 1
							FROM ms_vpl_product_detail
							WHERE cpnyid = p_cpny_id
								AND product_id = r_detail.product_id
								AND expired_date IS NOT DISTINCT FROM r_detail.expired_date
								AND whs_id = r_detail.whs_id
								AND COALESCE(qty_available, 0) < 0
					) THEN
							RAISE EXCEPTION
									'Stock menjadi minus. Dokumen %, line %, product %, '
									'expired date %, warehouse %.',
									p_docid,
									r_detail.linenbr,
									r_detail.product_id,
									r_detail.expired_date,
									r_detail.whs_id;
					END IF;

			END LOOP;

			IF v_row_count = 0 THEN
					RAISE EXCEPTION
							'Dokumen % tidak mempunyai detail penerimaan.',
							p_docid;
			END IF;

			RAISE NOTICE
					'Proses VPL berhasil. Dokumen %, activity %, jumlah detail %.',
					p_docid,
					v_activity,
					v_row_count;
					
    ELSIF UPPER(TRIM(p_doctype)) = 'VPT' THEN

        /*
        ================================================================
        DOCTYPE VPT
        TRANSFER ANTAR WAREHOUSE
        ================================================================
        */

        v_row_count := 0;

    /*
    ================================================================
    AMBIL HEADER TRANSFER
    ================================================================
    */

--     SELECT
--         h.cpnyid,
--         TO_CHAR(h.transfer_date, 'YYYYMM'),
--         TO_CHAR(h.transfer_date, 'YYYY'),
--         EXTRACT(MONTH FROM h.transfer_date)::INTEGER
--     INTO
--         v_cpnyid,
--         v_perpost,
--         v_year,
--         v_month
--     FROM tr_vpl_transfer h
--     WHERE h.transfer_id = p_docid
--       AND h.cpnyid = p_cpny_id
--     FOR UPDATE;
-- 
--     IF NOT FOUND THEN
--         RAISE EXCEPTION
--             'Dokumen transfer % dengan company % tidak ditemukan.',
--             p_docid,
--             p_cpny_id;
--     END IF;
-- 
--     IF v_perpost IS NULL THEN
--         RAISE EXCEPTION
--             'Transfer date dokumen % tidak boleh kosong.',
--             p_docid;
--     END IF;

		SELECT
				h.cpnyid
		INTO
				v_cpnyid
		FROM tr_vpl_transfer h
		WHERE h.transfer_id = p_docid
			AND h.cpnyid = p_cpny_id
		FOR UPDATE;

		IF NOT FOUND THEN
				RAISE EXCEPTION
						'Dokumen transfer % dengan company % tidak ditemukan.',
						p_docid,
						p_cpny_id;
		END IF;

		/*
		================================================================
		PERIODE POSTING MENGGUNAKAN DATETIME SAAT PROSES
		================================================================
		*/

		v_perpost := TO_CHAR(CURRENT_TIMESTAMP, 'YYYYMM');
		v_year    := TO_CHAR(CURRENT_TIMESTAMP, 'YYYY');
		v_month   := EXTRACT(MONTH FROM CURRENT_TIMESTAMP)::INTEGER;
		v_post_date := CURRENT_TIMESTAMP;

    /*
    ================================================================
    LOOP SETIAP LINE DETAIL TRANSFER
    ================================================================
    */

    FOR r_detail IN
        SELECT
            d.id,
            d.transfer_id,
            d.linenbr,
            d.product_id,
            d.expired_date,
            d.from_whs_id,
            d.to_whs_id,
            COALESCE(d.qty_transfer, 0) AS qty_transfer,
            d.ref_transfer_id,
            h.transfer_date
        FROM tr_vpl_transfer_detail d
        INNER JOIN tr_vpl_transfer h
            ON h.transfer_id = d.transfer_id
        WHERE d.transfer_id = p_docid
          AND h.cpnyid = p_cpny_id
        ORDER BY
            d.linenbr,
            d.id
        FOR UPDATE OF d
    LOOP
        v_row_count := v_row_count + 1;

        /*
        ============================================================
        VALIDASI DETAIL TRANSFER
        ============================================================
        */

        IF r_detail.linenbr IS NULL THEN
            RAISE EXCEPTION
                'Line number kosong pada detail ID %, dokumen %.',
                r_detail.id,
                p_docid;
        END IF;

        IF NULLIF(TRIM(r_detail.product_id), '') IS NULL THEN
            RAISE EXCEPTION
                'Product ID kosong pada dokumen %, line %.',
                p_docid,
                r_detail.linenbr;
        END IF;

        IF NULLIF(TRIM(r_detail.from_whs_id), '') IS NULL THEN
            RAISE EXCEPTION
                'Warehouse asal kosong pada dokumen %, line %.',
                p_docid,
                r_detail.linenbr;
        END IF;

        IF NULLIF(TRIM(r_detail.to_whs_id), '') IS NULL THEN
            RAISE EXCEPTION
                'Warehouse tujuan kosong pada dokumen %, line %.',
                p_docid,
                r_detail.linenbr;
        END IF;

        IF r_detail.from_whs_id = r_detail.to_whs_id THEN
            RAISE EXCEPTION
                'Warehouse asal dan tujuan tidak boleh sama. '
                'Dokumen %, line %, warehouse %.',
                p_docid,
                r_detail.linenbr,
                r_detail.from_whs_id;
        END IF;

        IF r_detail.qty_transfer <= 0 THEN
            RAISE EXCEPTION
                'Qty transfer harus lebih besar dari 0. '
                'Dokumen %, line %, qty %.',
                p_docid,
                r_detail.linenbr,
                r_detail.qty_transfer;
        END IF;

            /*
            ============================================================
            VALIDASI STATUS MASTER PRODUCT & WAREHOUSE (added 2026-08-10, SPM-02 fix)

            Hanya diblokir jika master DITEMUKAN dan berstatus non-aktif.
            Baris master yang tidak ditemukan sama sekali tidak diblokir
            di sini (di luar scope perbaikan ini).
            ============================================================
            */

            IF EXISTS (
                SELECT 1 FROM ms_vpl_product p
                WHERE p.product_id = r_detail.product_id
                  AND p.cpnyid = p_cpny_id
                  AND COALESCE(p.status, '') <> 'A'
            ) THEN
                RAISE EXCEPTION
                    'Product % (company %) berstatus tidak aktif. Dokumen %, line %.',
                    r_detail.product_id, p_cpny_id, p_docid, r_detail.linenbr;
            END IF;

            IF EXISTS (
                SELECT 1 FROM ms_vpl_warehouse w
                WHERE w.whs_id = r_detail.from_whs_id
                  AND w.cpnyid = p_cpny_id
                  AND COALESCE(w.status, '') <> 'A'
            ) THEN
                RAISE EXCEPTION
                    'Warehouse % (company %) berstatus tidak aktif. Dokumen %, line %.',
                    r_detail.from_whs_id, p_cpny_id, p_docid, r_detail.linenbr;
            END IF;

            IF EXISTS (
                SELECT 1 FROM ms_vpl_warehouse w
                WHERE w.whs_id = r_detail.to_whs_id
                  AND w.cpnyid = p_cpny_id
                  AND COALESCE(w.status, '') <> 'A'
            ) THEN
                RAISE EXCEPTION
                    'Warehouse % (company %) berstatus tidak aktif. Dokumen %, line %.',
                    r_detail.to_whs_id, p_cpny_id, p_docid, r_detail.linenbr;
            END IF;

        /*
        ============================================================
        CEK NET LEDGER WAREHOUSE ASAL PER LINE

        Penguncian transaksi:
        - refnbr
        - cpnyid
        - linenbr
        - product_id
        - expired_date
        - whs_id
        ============================================================
        */

        SELECT COALESCE(SUM(l.qty), 0)
        INTO v_qty
        FROM tr_vpl_ledger l
        WHERE l.refnbr = p_docid
          AND l.cpnyid = p_cpny_id
          AND l.linenbr = r_detail.linenbr
          AND l.product_id = r_detail.product_id
          AND l.expired_date IS NOT DISTINCT FROM r_detail.expired_date
          AND l.whs_id = r_detail.from_whs_id
          AND l.status = 'A';

        IF v_activity = 'Submit' THEN

            /*
            Pada Submit, warehouse asal dan tujuan belum boleh
            memiliki transaksi aktif untuk line tersebut.
            */

            IF v_qty <> 0 THEN
                RAISE EXCEPTION
                    'Line transfer sudah pernah diproses. '
                    'Dokumen %, line %, product %, warehouse asal %, net ledger %.',
                    p_docid,
                    r_detail.linenbr,
                    r_detail.product_id,
                    r_detail.from_whs_id,
                    v_qty;
            END IF;

            IF EXISTS
            (
                SELECT 1
                FROM tr_vpl_ledger l
                WHERE l.refnbr = p_docid
                  AND l.cpnyid = p_cpny_id
                  AND l.linenbr = r_detail.linenbr
                  AND l.product_id = r_detail.product_id
                  AND l.expired_date IS NOT DISTINCT FROM r_detail.expired_date
                  AND l.whs_id = r_detail.to_whs_id
                  AND l.status = 'A'
                GROUP BY
                    l.refnbr,
                    l.cpnyid,
                    l.linenbr,
                    l.product_id,
                    l.expired_date,
                    l.whs_id
                HAVING COALESCE(SUM(l.qty), 0) <> 0
            ) THEN
                RAISE EXCEPTION
                    'Warehouse tujuan line transfer sudah memiliki ledger aktif. '
                    'Dokumen %, line %, product %, warehouse tujuan %.',
                    p_docid,
                    r_detail.linenbr,
                    r_detail.product_id,
                    r_detail.to_whs_id;
            END IF;

            v_qty := r_detail.qty_transfer;

        ELSE

            /*
            Setelah Submit:
            warehouse asal net ledger harus negatif.
            warehouse tujuan net ledger harus positif.
            */

            IF v_qty >= 0 THEN
                RAISE EXCEPTION
                    'Transfer tidak dapat di-%. '
                    'Warehouse asal sudah tidak mempunyai transaksi transfer aktif. '
                    'Dokumen %, line %, product %, warehouse asal %, net ledger %.',
                    LOWER(v_activity),
                    p_docid,
                    r_detail.linenbr,
                    r_detail.product_id,
                    r_detail.from_whs_id,
                    v_qty;
            END IF;

            /*
            Simpan jumlah transfer aktif berdasarkan ledger warehouse asal.
            */

            v_qty := ABS(v_qty);

            /*
            Pastikan ledger warehouse tujuan sesuai dengan warehouse asal.
            */

            IF NOT EXISTS
            (
                SELECT 1
                FROM tr_vpl_ledger l
                WHERE l.refnbr = p_docid
                  AND l.cpnyid = p_cpny_id
                  AND l.linenbr = r_detail.linenbr
                  AND l.product_id = r_detail.product_id
                  AND l.expired_date IS NOT DISTINCT FROM r_detail.expired_date
                  AND l.whs_id = r_detail.to_whs_id
                  AND l.status = 'A'
                GROUP BY
                    l.refnbr,
                    l.cpnyid,
                    l.linenbr,
                    l.product_id,
                    l.expired_date,
                    l.whs_id
                HAVING COALESCE(SUM(l.qty), 0) = v_qty
            ) THEN
                RAISE EXCEPTION
                    'Ledger warehouse tujuan tidak sesuai dengan warehouse asal. '
                    'Dokumen %, line %, product %, warehouse tujuan %.',
                    p_docid,
                    r_detail.linenbr,
                    r_detail.product_id,
                    r_detail.to_whs_id;
            END IF;

        END IF;

        /*
        ============================================================
        ACTIVITY 1
        MEMBUAT DUA TR_VPL_LEDGER
        ============================================================
        */

        /*
        Ledger warehouse asal.

        Submit:
            qty negatif.

        Reject/Revise:
            qty positif.
        */

        INSERT INTO tr_vpl_ledger
        (
            refnbr,
            refdate,
            reference_refnbr,
            cpnyid,
            postdate,
            perpost,
            linenbr,
            product_id,
            expired_date,
            whs_id,
            purpose_id,
            transaction_source,
            transaction_activity,
            qty,
            status,
            created_user,
            created_at
        )
        VALUES
        (
            p_docid,
            v_post_date,
            r_detail.ref_transfer_id,
            p_cpny_id,
            v_post_date,
            v_perpost,
            r_detail.linenbr,
            r_detail.product_id,
            r_detail.expired_date,
            r_detail.from_whs_id,
            NULL,
            'Transfer Out',
            v_activity,
            CASE
                WHEN v_activity = 'Submit' THEN v_qty * -1
                ELSE v_qty
            END,
            'A',
            p_user,
            CURRENT_TIMESTAMP
        );

        /*
        Ledger warehouse tujuan.

        Submit:
            qty positif.

        Reject/Revise:
            qty negatif.
        */

        INSERT INTO tr_vpl_ledger
        (
            refnbr,
            refdate,
            reference_refnbr,
            cpnyid,
            postdate,
            perpost,
            linenbr,
            product_id,
            expired_date,
            whs_id,
            purpose_id,
            transaction_source,
            transaction_activity,
            qty,
            status,
            created_user,
            created_at
        )
        VALUES
        (
            p_docid,
            v_post_date,
            r_detail.ref_transfer_id,
            p_cpny_id,
            v_post_date,
            v_perpost,
            r_detail.linenbr,
            r_detail.product_id,
            r_detail.expired_date,
            r_detail.to_whs_id,
            NULL,
            'Transfer In',
            v_activity,
            CASE
                WHEN v_activity = 'Submit' THEN v_qty
                ELSE v_qty * -1
            END,
            'A',
            p_user,
            CURRENT_TIMESTAMP
        );

        /*
        ============================================================
        ACTIVITY 2
        PASTIKAN STOCK BALANCE ASAL DAN TUJUAN SUDAH ADA

        Jika record belum ada, dibuat terlebih dahulu dengan seluruh
        pergerakan bulan bernilai 0. Setelah itu baru dilakukan update.
        ============================================================
        */

        INSERT INTO ms_vpl_product_bal
        (
            year,
            perpost,
            product_id,
            expired_date,
            cpnyid,
            whs_id,
            begqty,
            period01in, period01out,
            period02in, period02out,
            period03in, period03out,
            period04in, period04out,
            period05in, period05out,
            period06in, period06out,
            period07in, period07out,
            period08in, period08out,
            period09in, period09out,
            period10in, period10out,
            period11in, period11out,
            period12in, period12out,
            status,
            created_user,
            created_at
        )
        SELECT
            v_year,
            v_perpost,
            r_detail.product_id,
            r_detail.expired_date,
            p_cpny_id,
            r_detail.from_whs_id,
            0,
            0, 0,
            0, 0,
            0, 0,
            0, 0,
            0, 0,
            0, 0,
            0, 0,
            0, 0,
            0, 0,
            0, 0,
            0, 0,
            0, 0,
            'A',
            p_user,
            CURRENT_TIMESTAMP
        WHERE NOT EXISTS
        (
            SELECT 1
            FROM ms_vpl_product_bal b
            WHERE b.year = v_year
              AND b.cpnyid = p_cpny_id
              AND b.product_id = r_detail.product_id
              AND b.expired_date IS NOT DISTINCT FROM r_detail.expired_date
              AND b.whs_id = r_detail.from_whs_id
        );

        INSERT INTO ms_vpl_product_bal
        (
            year,
            perpost,
            product_id,
            expired_date,
            cpnyid,
            whs_id,
            begqty,
            period01in, period01out,
            period02in, period02out,
            period03in, period03out,
            period04in, period04out,
            period05in, period05out,
            period06in, period06out,
            period07in, period07out,
            period08in, period08out,
            period09in, period09out,
            period10in, period10out,
            period11in, period11out,
            period12in, period12out,
            status,
            created_user,
            created_at
        )
        SELECT
            v_year,
            v_perpost,
            r_detail.product_id,
            r_detail.expired_date,
            p_cpny_id,
            r_detail.to_whs_id,
            0,
            0, 0,
            0, 0,
            0, 0,
            0, 0,
            0, 0,
            0, 0,
            0, 0,
            0, 0,
            0, 0,
            0, 0,
            0, 0,
            0, 0,
            'A',
            p_user,
            CURRENT_TIMESTAMP
        WHERE NOT EXISTS
        (
            SELECT 1
            FROM ms_vpl_product_bal b
            WHERE b.year = v_year
              AND b.cpnyid = p_cpny_id
              AND b.product_id = r_detail.product_id
              AND b.expired_date IS NOT DISTINCT FROM r_detail.expired_date
              AND b.whs_id = r_detail.to_whs_id
        );

        /*
        ============================================================
        ACTIVITY 2A
        UPDATE STOCK BALANCE WAREHOUSE ASAL

        Submit        : periodXXout bertambah.
        Reject/Revise : periodXXin bertambah.
        ============================================================
        */

        EXECUTE FORMAT(
            'UPDATE ms_vpl_product_bal
             SET perpost = $1,
                 period%1$sout = COALESCE(period%1$sout, 0)
                     + CASE WHEN $2 = ''Submit'' THEN $3 ELSE 0 END,
                 period%1$sin = COALESCE(period%1$sin, 0)
                     + CASE WHEN $2 IN (''Reject'', ''Revise'') THEN $3 ELSE 0 END,
                 updated_user = $4,
                 updated_at = CURRENT_TIMESTAMP
             WHERE year = $5
               AND cpnyid = $6
               AND product_id = $7
               AND expired_date IS NOT DISTINCT FROM $8
               AND whs_id = $9',
            LPAD(v_month::TEXT, 2, '0')
        )
        USING
            v_perpost,
            v_activity,
            v_qty,
            p_user,
            v_year,
            p_cpny_id,
            r_detail.product_id,
            r_detail.expired_date,
            r_detail.from_whs_id;

        /*
        ============================================================
        ACTIVITY 2B
        UPDATE STOCK BALANCE WAREHOUSE TUJUAN

        Submit        : periodXXin bertambah.
        Reject/Revise : periodXXout bertambah.
        ============================================================
        */

        EXECUTE FORMAT(
            'UPDATE ms_vpl_product_bal
             SET perpost = $1,
                 period%1$sin = COALESCE(period%1$sin, 0)
                     + CASE WHEN $2 = ''Submit'' THEN $3 ELSE 0 END,
                 period%1$sout = COALESCE(period%1$sout, 0)
                     + CASE WHEN $2 IN (''Reject'', ''Revise'') THEN $3 ELSE 0 END,
                 updated_user = $4,
                 updated_at = CURRENT_TIMESTAMP
             WHERE year = $5
               AND cpnyid = $6
               AND product_id = $7
               AND expired_date IS NOT DISTINCT FROM $8
               AND whs_id = $9',
            LPAD(v_month::TEXT, 2, '0')
        )
        USING
            v_perpost,
            v_activity,
            v_qty,
            p_user,
            v_year,
            p_cpny_id,
            r_detail.product_id,
            r_detail.expired_date,
            r_detail.to_whs_id;

        /*
        ============================================================
        ACTIVITY 3
        PASTIKAN STOCK DETAIL ASAL DAN TUJUAN SUDAH ADA

        Record baru dibuat dengan qty_available = 0.
        ============================================================
        */

        INSERT INTO ms_vpl_product_detail
        (
            product_id,
            expired_date,
            cpnyid,
            whs_id,
            qty_available,
            target_date,
            status,
            created_user,
            created_at
        )
        SELECT
            r_detail.product_id,
            r_detail.expired_date,
            p_cpny_id,
            r_detail.from_whs_id,
            0,
            NULL,
            'A',
            p_user,
            CURRENT_TIMESTAMP
        WHERE NOT EXISTS
        (
            SELECT 1
            FROM ms_vpl_product_detail d
            WHERE d.cpnyid = p_cpny_id
              AND d.product_id = r_detail.product_id
              AND d.expired_date IS NOT DISTINCT FROM r_detail.expired_date
              AND d.whs_id = r_detail.from_whs_id
        );

        INSERT INTO ms_vpl_product_detail
        (
            product_id,
            expired_date,
            cpnyid,
            whs_id,
            qty_available,
            target_date,
            status,
            created_user,
            created_at
        )
        SELECT
            r_detail.product_id,
            r_detail.expired_date,
            p_cpny_id,
            r_detail.to_whs_id,
            0,
            NULL,
            'A',
            p_user,
            CURRENT_TIMESTAMP
        WHERE NOT EXISTS
        (
            SELECT 1
            FROM ms_vpl_product_detail d
            WHERE d.cpnyid = p_cpny_id
              AND d.product_id = r_detail.product_id
              AND d.expired_date IS NOT DISTINCT FROM r_detail.expired_date
              AND d.whs_id = r_detail.to_whs_id
        );

        /*
        ============================================================
        ACTIVITY 3A
        UPDATE STOCK QTY WAREHOUSE ASAL
        ============================================================
        */

        IF v_activity = 'Submit' THEN

            UPDATE ms_vpl_product_detail
            SET
                qty_available = COALESCE(qty_available, 0) - v_qty,
                updated_user = p_user,
                updated_at = CURRENT_TIMESTAMP
            WHERE cpnyid = p_cpny_id
              AND product_id = r_detail.product_id
              AND expired_date IS NOT DISTINCT FROM r_detail.expired_date
              AND whs_id = r_detail.from_whs_id
              AND COALESCE(qty_available, 0) >= v_qty;

            IF NOT FOUND THEN
                RAISE EXCEPTION
                    'Stock warehouse asal tidak mencukupi. '
                    'Dokumen %, line %, product %, warehouse %, qty transfer %.',
                    p_docid,
                    r_detail.linenbr,
                    r_detail.product_id,
                    r_detail.from_whs_id,
                    v_qty;
            END IF;

        ELSE

            UPDATE ms_vpl_product_detail
            SET
                qty_available = COALESCE(qty_available, 0) + v_qty,
                updated_user = p_user,
                updated_at = CURRENT_TIMESTAMP
            WHERE cpnyid = p_cpny_id
              AND product_id = r_detail.product_id
              AND expired_date IS NOT DISTINCT FROM r_detail.expired_date
              AND whs_id = r_detail.from_whs_id;

        END IF;

        /*
        ============================================================
        ACTIVITY 3B
        UPDATE STOCK QTY WAREHOUSE TUJUAN
        ============================================================
        */

        IF v_activity = 'Submit' THEN

            UPDATE ms_vpl_product_detail
            SET
                qty_available = COALESCE(qty_available, 0) + v_qty,
                updated_user = p_user,
                updated_at = CURRENT_TIMESTAMP
            WHERE cpnyid = p_cpny_id
              AND product_id = r_detail.product_id
              AND expired_date IS NOT DISTINCT FROM r_detail.expired_date
              AND whs_id = r_detail.to_whs_id;

        ELSE

            UPDATE ms_vpl_product_detail
            SET
                qty_available = COALESCE(qty_available, 0) - v_qty,
                updated_user = p_user,
                updated_at = CURRENT_TIMESTAMP
            WHERE cpnyid = p_cpny_id
              AND product_id = r_detail.product_id
              AND expired_date IS NOT DISTINCT FROM r_detail.expired_date
              AND whs_id = r_detail.to_whs_id
              AND COALESCE(qty_available, 0) >= v_qty;

            IF NOT FOUND THEN
                RAISE EXCEPTION
                    'Stock warehouse tujuan tidak mencukupi untuk pembalikan transfer. '
                    'Dokumen %, line %, product %, warehouse %, qty pembalikan %.',
                    p_docid,
                    r_detail.linenbr,
                    r_detail.product_id,
                    r_detail.to_whs_id,
                    v_qty;
            END IF;

        END IF;

    END LOOP;

    IF v_row_count = 0 THEN
        RAISE EXCEPTION
            'Dokumen transfer % tidak mempunyai detail.',
            p_docid;
    END IF;

    RAISE NOTICE
        'Proses VPT berhasil. Dokumen %, activity %, jumlah detail %.',
        p_docid,
        v_activity,
        v_row_count;
		
    ELSIF UPPER(TRIM(p_doctype)) = 'VPU' THEN

			/*
			================================================================
			DOCTYPE VPU
			USAGE / PENGELUARAN VOUCHER
			================================================================
			*/

			v_row_count := 0;

			/*
			================================================================
			AMBIL HEADER USAGE
			================================================================
			*/

-- 			SELECT
-- 					h.cpnyid,
-- 					TO_CHAR(h.usage_date, 'YYYYMM'),
-- 					TO_CHAR(h.usage_date, 'YYYY'),
-- 					EXTRACT(MONTH FROM h.usage_date)::INTEGER,
-- 					h.usagetype
-- 			INTO
-- 					v_cpnyid,
-- 					v_perpost,
-- 					v_year,
-- 					v_month,
-- 					v_usagetype
-- 			FROM tr_vpl_usage h
-- 			WHERE h.usage_id = p_docid
-- 				AND h.cpnyid = p_cpny_id
-- 			FOR UPDATE;
-- 
-- 			IF NOT FOUND THEN
-- 					RAISE EXCEPTION
-- 							'Dokumen usage % dengan company % tidak ditemukan.',
-- 							p_docid,
-- 							p_cpny_id;
-- 			END IF;
-- 
-- 			IF v_perpost IS NULL THEN
-- 					RAISE EXCEPTION
-- 							'Usage date dokumen % tidak boleh kosong.',
-- 							p_docid;
-- 			END IF;
-- 
-- 			IF v_usagetype NOT IN ('Usage', 'Return') THEN
-- 					RAISE EXCEPTION
-- 							'Usagetype % pada dokumen % tidak dikenali (harus Usage atau Return).',
-- 							v_usagetype,
-- 							p_docid;
-- 			END IF;


			SELECT
					h.cpnyid,
					h.usage_date,
					h.usagetype,
					h.department
			INTO
					v_cpnyid,
					v_usage_date,
					v_usagetype,
					v_department
			FROM tr_vpl_usage h
			WHERE h.usage_id = p_docid
				AND h.cpnyid = p_cpny_id
			FOR UPDATE;

			IF NOT FOUND THEN
					RAISE EXCEPTION
							'Dokumen usage % dengan company % tidak ditemukan.',
							p_docid,
							p_cpny_id;
			END IF;

			IF v_usagetype NOT IN ('Usage', 'Return') THEN
					RAISE EXCEPTION
							'Usagetype % pada dokumen % tidak dikenali (harus Usage atau Return).',
							v_usagetype,
							p_docid;
			END IF;
				
			/*
			================================================================
			PENENTUAN PERIODE POSTING
			CUSTOMERSERVICE: pakai usage_date
			Selain CUSTOMERSERVICE: pakai datetime saat SP dijalankan
			================================================================
			*/
		
			IF UPPER(TRIM(COALESCE(v_department, ''))) = 'CUSTOMERSERVICE' THEN

					IF v_usage_date IS NULL THEN
							RAISE EXCEPTION
									'Usage date dokumen % tidak boleh kosong untuk department CUSTOMERSERVICE.',
									p_docid;
					END IF;

					v_perpost := TO_CHAR(v_usage_date, 'YYYYMM');
					v_year    := TO_CHAR(v_usage_date, 'YYYY');
					v_month   := EXTRACT(MONTH FROM v_usage_date)::INTEGER;
					v_post_date := v_usage_date;

			ELSE

					v_perpost := TO_CHAR(CURRENT_TIMESTAMP, 'YYYYMM');
					v_year    := TO_CHAR(CURRENT_TIMESTAMP, 'YYYY');
					v_month   := EXTRACT(MONTH FROM CURRENT_TIMESTAMP)::INTEGER;
					v_post_date := CURRENT_TIMESTAMP;

			END IF;

			/*
			================================================================
			LOOP SETIAP LINE DETAIL USAGE
			================================================================
			*/

			FOR r_detail IN
					SELECT
							d.id,
							d.usage_id,
							d.linenbr,
							d.product_id,
							d.expired_date,
							d.whs_id,
							COALESCE(d.qty_usage, 0) AS qty_usage,
							COALESCE(d.qty_return_usage, 0) AS qty_return_usage,
							d.purpose_id,
							d.ref_usage_id,
							h.usage_date
					FROM tr_vpl_usage_detail d
					INNER JOIN tr_vpl_usage h
							ON h.usage_id = d.usage_id
					WHERE d.usage_id = p_docid
						AND h.cpnyid = p_cpny_id
					ORDER BY
							d.linenbr,
							d.id
					FOR UPDATE OF d
			LOOP

					v_row_count := v_row_count + 1;

					/*
					============================================================
					VALIDASI DETAIL
					============================================================
					*/

					IF r_detail.linenbr IS NULL THEN
							RAISE EXCEPTION
									'Line number kosong pada detail ID %, dokumen %.',
									r_detail.id,
									p_docid;
					END IF;

					IF NULLIF(TRIM(r_detail.product_id), '') IS NULL THEN
							RAISE EXCEPTION
									'Product ID kosong pada dokumen %, line %.',
									p_docid,
									r_detail.linenbr;
					END IF;

					IF NULLIF(TRIM(r_detail.whs_id), '') IS NULL THEN
							RAISE EXCEPTION
									'Warehouse ID kosong pada dokumen %, line %.',
									p_docid,
									r_detail.linenbr;
					END IF;

					IF v_usagetype = 'Usage' AND r_detail.qty_usage <= 0 THEN
							RAISE EXCEPTION
									'Qty usage harus lebih besar dari 0. '
									'Dokumen %, line %, qty %.',
									p_docid,
									r_detail.linenbr,
									r_detail.qty_usage;
					END IF;

					IF v_usagetype = 'Return' AND r_detail.qty_return_usage <= 0 THEN
							RAISE EXCEPTION
									'Qty return usage harus lebih besar dari 0. '
									'Dokumen %, line %, qty %.',
									p_docid,
									r_detail.linenbr,
									r_detail.qty_return_usage;
					END IF;

            /*
            ============================================================
            VALIDASI STATUS MASTER PRODUCT & WAREHOUSE (added 2026-08-10, SPM-02 fix)

            Hanya diblokir jika master DITEMUKAN dan berstatus non-aktif.
            Baris master yang tidak ditemukan sama sekali tidak diblokir
            di sini (di luar scope perbaikan ini).
            ============================================================
            */

            IF EXISTS (
                SELECT 1 FROM ms_vpl_product p
                WHERE p.product_id = r_detail.product_id
                  AND p.cpnyid = p_cpny_id
                  AND COALESCE(p.status, '') <> 'A'
            ) THEN
                RAISE EXCEPTION
                    'Product % (company %) berstatus tidak aktif. Dokumen %, line %.',
                    r_detail.product_id, p_cpny_id, p_docid, r_detail.linenbr;
            END IF;

            IF EXISTS (
                SELECT 1 FROM ms_vpl_warehouse w
                WHERE w.whs_id = r_detail.whs_id
                  AND w.cpnyid = p_cpny_id
                  AND COALESCE(w.status, '') <> 'A'
            ) THEN
                RAISE EXCEPTION
                    'Warehouse % (company %) berstatus tidak aktif. Dokumen %, line %.',
                    r_detail.whs_id, p_cpny_id, p_docid, r_detail.linenbr;
            END IF;

					/*
					============================================================
					CEK NET LEDGER PER LINE

					Key:
					- refnbr
					- cpnyid
					- linenbr
					- product_id
					- expired_date
					- whs_id

					Submit Usage       = net ledger NEGATIF
					Reject / Revise    = membalikkan menjadi 0
					============================================================
					*/

					SELECT COALESCE(SUM(l.qty), 0)
					INTO v_qty
					FROM tr_vpl_ledger l
					WHERE l.refnbr = p_docid
						AND l.cpnyid = p_cpny_id
						AND l.linenbr = r_detail.linenbr
						AND l.product_id = r_detail.product_id
						AND l.expired_date IS NOT DISTINCT FROM r_detail.expired_date
						AND l.whs_id = r_detail.whs_id
						AND l.status = 'A';

					/*
					============================================================
					TENTUKAN QTY TRANSAKSI
					============================================================
					*/

					IF v_activity = 'Submit' THEN

							/*
							Belum boleh ada saldo ledger aktif.
							*/

							IF v_qty <> 0 THEN
									RAISE EXCEPTION
											'Line usage sudah pernah Submit dan belum dibalikkan. '
											'Dokumen %, line %, product %, expired date %, '
											'warehouse %, net ledger %.',
											p_docid,
											r_detail.linenbr,
											r_detail.product_id,
											r_detail.expired_date,
											r_detail.whs_id,
											v_qty;
							END IF;

							/*
							Qty transaksi disimpan positif dulu.
							Pada insert ledger nanti dijadikan negatif untuk Usage,
							positif untuk Return.
							*/

							v_qty := CASE
									WHEN v_usagetype = 'Return' THEN r_detail.qty_return_usage
									ELSE r_detail.qty_usage
							END;

					ELSIF v_activity IN ('Reject', 'Revise') THEN

							/*
							Usage aktif harus mempunyai net ledger negatif;
							Return aktif harus mempunyai net ledger positif
							(kebalikan dari Usage, karena Submit-Return menulis qty positif).
							*/

							IF (v_usagetype = 'Usage' AND v_qty >= 0)
								OR (v_usagetype = 'Return' AND v_qty <= 0) THEN
									RAISE EXCEPTION
											'% tidak dapat di-%. '
											'Dokumen sudah tidak mempunyai % aktif. '
											'Dokumen %, line %, product %, expired date %, '
											'warehouse %, net ledger %.',
											v_usagetype,
											LOWER(v_activity),
											LOWER(v_usagetype),
											p_docid,
											r_detail.linenbr,
											r_detail.product_id,
											r_detail.expired_date,
											r_detail.whs_id,
											v_qty;
							END IF;

							/*
							Qty pembalikan berasal dari net ledger aktif.
							*/

							v_qty := ABS(v_qty);

					END IF;


					/*
					============================================================
					ACTIVITY 1
					INSERT TR_VPL_LEDGER
					============================================================

					Usage  Submit: qty = -v_qty | Reject/Revise: qty = +v_qty
					Return Submit: qty = +v_qty | Reject/Revise: qty = -v_qty
					(Return membalikkan arah stock dibanding Usage)
					============================================================
					*/

					INSERT INTO tr_vpl_ledger
					(
							refnbr,
							refdate,
							reference_refnbr,
							cpnyid,
							postdate,
							perpost,
							linenbr,
							product_id,
							expired_date,
							whs_id,
							purpose_id,
							transaction_source,
							transaction_activity,
							qty,
							status,
							created_user,
							created_at
					)
					VALUES
					(
							p_docid,
							v_post_date,
							r_detail.ref_usage_id,
							p_cpny_id,
							v_post_date,
							v_perpost,
							r_detail.linenbr,
							r_detail.product_id,
							r_detail.expired_date,
							r_detail.whs_id,
							r_detail.purpose_id,
							v_usagetype,
							v_activity,

							CASE
									WHEN v_activity = 'Submit' AND v_usagetype = 'Usage'
											THEN v_qty * -1
									WHEN v_activity = 'Submit' AND v_usagetype = 'Return'
											THEN v_qty
									WHEN v_activity IN ('Reject', 'Revise') AND v_usagetype = 'Usage'
											THEN v_qty
									ELSE v_qty * -1
							END,

							'A',
							p_user,
							CURRENT_TIMESTAMP
					);


					/*
					============================================================
					ACTIVITY 2
					PASTIKAN STOCK BALANCE ADA

					Jika belum ada, create dahulu dengan semua period = 0.
					============================================================
					*/

					IF NOT EXISTS
					(
							SELECT 1
							FROM ms_vpl_product_bal b
							WHERE b.year = v_year
								AND b.cpnyid = p_cpny_id
								AND b.product_id = r_detail.product_id
								AND b.expired_date IS NOT DISTINCT FROM r_detail.expired_date
								AND b.whs_id = r_detail.whs_id
					) THEN

							INSERT INTO ms_vpl_product_bal
							(
									year,
									perpost,
									product_id,
									expired_date,
									cpnyid,
									whs_id,
									begqty,

									period01in,
									period01out,
									period02in,
									period02out,
									period03in,
									period03out,
									period04in,
									period04out,
									period05in,
									period05out,
									period06in,
									period06out,
									period07in,
									period07out,
									period08in,
									period08out,
									period09in,
									period09out,
									period10in,
									period10out,
									period11in,
									period11out,
									period12in,
									period12out,

									status,
									created_user,
									created_at
							)
							VALUES
							(
									v_year,
									v_perpost,
									r_detail.product_id,
									r_detail.expired_date,
									p_cpny_id,
									r_detail.whs_id,
									0,

									0, 0,
									0, 0,
									0, 0,
									0, 0,
									0, 0,
									0, 0,
									0, 0,
									0, 0,
									0, 0,
									0, 0,
									0, 0,
									0, 0,

									'A',
									p_user,
									CURRENT_TIMESTAMP
							);

					END IF;


					/*
					============================================================
					ACTIVITY 2
					UPDATE MONTHLY STOCK BALANCE

					Submit:
							periodXXout + qty

					Reject / Revise:
							periodXXin + qty
					============================================================
					*/

					UPDATE ms_vpl_product_bal
					SET
							perpost = v_perpost,

							period01in =
							COALESCE(period01in, 0)
							+ CASE
									WHEN v_month = 1
									 AND (
										 (v_usagetype = 'Usage' AND v_activity IN ('Reject', 'Revise'))
										 OR (v_usagetype = 'Return' AND v_activity = 'Submit')
									 )
									THEN v_qty
									ELSE 0
								END,

						period01out =
								COALESCE(period01out, 0)
								+ CASE
										WHEN v_month = 1
										 AND (
											 (v_usagetype = 'Usage' AND v_activity = 'Submit')
											 OR (v_usagetype = 'Return' AND v_activity IN ('Reject', 'Revise'))
										 )
										THEN v_qty
										ELSE 0
									END,


							period02in =
							COALESCE(period02in, 0)
							+ CASE
									WHEN v_month = 2
									 AND (
										 (v_usagetype = 'Usage' AND v_activity IN ('Reject', 'Revise'))
										 OR (v_usagetype = 'Return' AND v_activity = 'Submit')
									 )
									THEN v_qty
									ELSE 0
								END,

						period02out =
								COALESCE(period02out, 0)
								+ CASE
										WHEN v_month = 2
										 AND (
											 (v_usagetype = 'Usage' AND v_activity = 'Submit')
											 OR (v_usagetype = 'Return' AND v_activity IN ('Reject', 'Revise'))
										 )
										THEN v_qty
										ELSE 0
									END,


							period03in =
							COALESCE(period03in, 0)
							+ CASE
									WHEN v_month = 3
									 AND (
										 (v_usagetype = 'Usage' AND v_activity IN ('Reject', 'Revise'))
										 OR (v_usagetype = 'Return' AND v_activity = 'Submit')
									 )
									THEN v_qty
									ELSE 0
								END,

						period03out =
								COALESCE(period03out, 0)
								+ CASE
										WHEN v_month = 3
										 AND (
											 (v_usagetype = 'Usage' AND v_activity = 'Submit')
											 OR (v_usagetype = 'Return' AND v_activity IN ('Reject', 'Revise'))
										 )
										THEN v_qty
										ELSE 0
									END,


							period04in =
							COALESCE(period04in, 0)
							+ CASE
									WHEN v_month = 4
									 AND (
										 (v_usagetype = 'Usage' AND v_activity IN ('Reject', 'Revise'))
										 OR (v_usagetype = 'Return' AND v_activity = 'Submit')
									 )
									THEN v_qty
									ELSE 0
								END,

						period04out =
								COALESCE(period04out, 0)
								+ CASE
										WHEN v_month = 4
										 AND (
											 (v_usagetype = 'Usage' AND v_activity = 'Submit')
											 OR (v_usagetype = 'Return' AND v_activity IN ('Reject', 'Revise'))
										 )
										THEN v_qty
										ELSE 0
									END,


							period05in =
							COALESCE(period05in, 0)
							+ CASE
									WHEN v_month = 5
									 AND (
										 (v_usagetype = 'Usage' AND v_activity IN ('Reject', 'Revise'))
										 OR (v_usagetype = 'Return' AND v_activity = 'Submit')
									 )
									THEN v_qty
									ELSE 0
								END,

						period05out =
								COALESCE(period05out, 0)
								+ CASE
										WHEN v_month = 5
										 AND (
											 (v_usagetype = 'Usage' AND v_activity = 'Submit')
											 OR (v_usagetype = 'Return' AND v_activity IN ('Reject', 'Revise'))
										 )
										THEN v_qty
										ELSE 0
									END,


							period06in =
							COALESCE(period06in, 0)
							+ CASE
									WHEN v_month = 6
									 AND (
										 (v_usagetype = 'Usage' AND v_activity IN ('Reject', 'Revise'))
										 OR (v_usagetype = 'Return' AND v_activity = 'Submit')
									 )
									THEN v_qty
									ELSE 0
								END,

						period06out =
								COALESCE(period06out, 0)
								+ CASE
										WHEN v_month = 6
										 AND (
											 (v_usagetype = 'Usage' AND v_activity = 'Submit')
											 OR (v_usagetype = 'Return' AND v_activity IN ('Reject', 'Revise'))
										 )
										THEN v_qty
										ELSE 0
									END,


							period07in =
							COALESCE(period07in, 0)
							+ CASE
									WHEN v_month = 7
									 AND (
										 (v_usagetype = 'Usage' AND v_activity IN ('Reject', 'Revise'))
										 OR (v_usagetype = 'Return' AND v_activity = 'Submit')
									 )
									THEN v_qty
									ELSE 0
								END,

						period07out =
								COALESCE(period07out, 0)
								+ CASE
										WHEN v_month = 7
										 AND (
											 (v_usagetype = 'Usage' AND v_activity = 'Submit')
											 OR (v_usagetype = 'Return' AND v_activity IN ('Reject', 'Revise'))
										 )
										THEN v_qty
										ELSE 0
									END,


							period08in =
							COALESCE(period08in, 0)
							+ CASE
									WHEN v_month = 8
									 AND (
										 (v_usagetype = 'Usage' AND v_activity IN ('Reject', 'Revise'))
										 OR (v_usagetype = 'Return' AND v_activity = 'Submit')
									 )
									THEN v_qty
									ELSE 0
								END,

						period08out =
								COALESCE(period08out, 0)
								+ CASE
										WHEN v_month = 8
										 AND (
											 (v_usagetype = 'Usage' AND v_activity = 'Submit')
											 OR (v_usagetype = 'Return' AND v_activity IN ('Reject', 'Revise'))
										 )
										THEN v_qty
										ELSE 0
									END,


							period09in =
							COALESCE(period09in, 0)
							+ CASE
									WHEN v_month = 9
									 AND (
										 (v_usagetype = 'Usage' AND v_activity IN ('Reject', 'Revise'))
										 OR (v_usagetype = 'Return' AND v_activity = 'Submit')
									 )
									THEN v_qty
									ELSE 0
								END,

						period09out =
								COALESCE(period09out, 0)
								+ CASE
										WHEN v_month = 9
										 AND (
											 (v_usagetype = 'Usage' AND v_activity = 'Submit')
											 OR (v_usagetype = 'Return' AND v_activity IN ('Reject', 'Revise'))
										 )
										THEN v_qty
										ELSE 0
									END,


							period10in =
							COALESCE(period10in, 0)
							+ CASE
									WHEN v_month = 10
									 AND (
										 (v_usagetype = 'Usage' AND v_activity IN ('Reject', 'Revise'))
										 OR (v_usagetype = 'Return' AND v_activity = 'Submit')
									 )
									THEN v_qty
									ELSE 0
								END,

						period10out =
								COALESCE(period10out, 0)
								+ CASE
										WHEN v_month = 10
										 AND (
											 (v_usagetype = 'Usage' AND v_activity = 'Submit')
											 OR (v_usagetype = 'Return' AND v_activity IN ('Reject', 'Revise'))
										 )
										THEN v_qty
										ELSE 0
									END,


							period11in =
							COALESCE(period11in, 0)
							+ CASE
									WHEN v_month = 11
									 AND (
										 (v_usagetype = 'Usage' AND v_activity IN ('Reject', 'Revise'))
										 OR (v_usagetype = 'Return' AND v_activity = 'Submit')
									 )
									THEN v_qty
									ELSE 0
								END,

						period11out =
								COALESCE(period11out, 0)
								+ CASE
										WHEN v_month = 11
										 AND (
											 (v_usagetype = 'Usage' AND v_activity = 'Submit')
											 OR (v_usagetype = 'Return' AND v_activity IN ('Reject', 'Revise'))
										 )
										THEN v_qty
										ELSE 0
									END,


							period12in =
							COALESCE(period12in, 0)
							+ CASE
									WHEN v_month = 12
									 AND (
										 (v_usagetype = 'Usage' AND v_activity IN ('Reject', 'Revise'))
										 OR (v_usagetype = 'Return' AND v_activity = 'Submit')
									 )
									THEN v_qty
									ELSE 0
								END,

						period12out =
								COALESCE(period12out, 0)
								+ CASE
										WHEN v_month = 12
										 AND (
											 (v_usagetype = 'Usage' AND v_activity = 'Submit')
											 OR (v_usagetype = 'Return' AND v_activity IN ('Reject', 'Revise'))
										 )
										THEN v_qty
										ELSE 0
									END,

							updated_user = p_user,
							updated_at = CURRENT_TIMESTAMP

					WHERE year = v_year
						AND cpnyid = p_cpny_id
						AND product_id = r_detail.product_id
						AND expired_date IS NOT DISTINCT FROM r_detail.expired_date
						AND whs_id = r_detail.whs_id;


					/*
					============================================================
					ACTIVITY 3
					UPDATE STOCK QTY
					============================================================
					*/

					/*
					Submit+Usage dan Reject/Revise+Return: stock berkurang
					(harus tersedia dan qty harus mencukupi).

					Reject/Revise+Usage dan Submit+Return: stock bertambah
					(create record jika belum ada).
					*/

					IF (v_activity = 'Submit' AND v_usagetype = 'Usage')
						OR (v_activity IN ('Reject', 'Revise') AND v_usagetype = 'Return') THEN

							UPDATE ms_vpl_product_detail
							SET
									qty_available =
											COALESCE(qty_available, 0) - v_qty,

									updated_user = p_user,
									updated_at = CURRENT_TIMESTAMP

							WHERE cpnyid = p_cpny_id
								AND product_id = r_detail.product_id
								AND expired_date IS NOT DISTINCT FROM r_detail.expired_date
								AND whs_id = r_detail.whs_id
								AND COALESCE(qty_available, 0) >= v_qty;

							IF NOT FOUND THEN
									RAISE EXCEPTION
											'Stock tidak ditemukan atau tidak mencukupi untuk %. '
											'Dokumen %, line %, product %, expired date %, '
											'warehouse %, qty %.',
											v_usagetype,
											p_docid,
											r_detail.linenbr,
											r_detail.product_id,
											r_detail.expired_date,
											r_detail.whs_id,
											v_qty;
							END IF;

					ELSE

							/*
							Reject/Revise+Usage: voucher dikembalikan ke stock.
							Submit+Return: barang yang dikembalikan menambah stock.
							*/

							UPDATE ms_vpl_product_detail
							SET
									qty_available =
											COALESCE(qty_available, 0) + v_qty,

									updated_user = p_user,
									updated_at = CURRENT_TIMESTAMP

							WHERE cpnyid = p_cpny_id
								AND product_id = r_detail.product_id
								AND expired_date IS NOT DISTINCT FROM r_detail.expired_date
								AND whs_id = r_detail.whs_id;

							/*
							Secara normal record pasti ada karena sebelumnya pernah Submit
							(untuk Reject/Revise+Usage). Untuk Submit+Return baris master
							mungkin belum pernah ada sama sekali. Jika tidak ada, create.
							*/

							IF NOT FOUND THEN

									INSERT INTO ms_vpl_product_detail
									(
											product_id,
											expired_date,
											cpnyid,
											whs_id,
											qty_available,
											target_date,
											status,
											created_user,
											created_at
									)
									VALUES
									(
											r_detail.product_id,
											r_detail.expired_date,
											p_cpny_id,
											r_detail.whs_id,
											v_qty,
											NULL,
											'A',
											p_user,
											CURRENT_TIMESTAMP
									);

							END IF;

					END IF;

			END LOOP;


			/*
			================================================================
			VALIDASI DETAIL
			================================================================
			*/

			IF v_row_count = 0 THEN
					RAISE EXCEPTION
							'Dokumen usage % tidak mempunyai detail.',
							p_docid;
			END IF;

			RAISE NOTICE
					'Proses VPU berhasil. Dokumen %, activity %, jumlah detail %.',
					p_docid,
					v_activity,
					v_row_count;
	

    ELSIF UPPER(TRIM(p_doctype)) = 'VPS' THEN

        /*
        ================================================================
        DOCTYPE VPS
        SETTLEMENT / PERTANGGUNGJAWABAN USAGE VOUCHER
        ================================================================
        */

        v_row_count := 0;
-- 
--         SELECT
--             h.cpnyid,
--             TO_CHAR(h.settlement_date, 'YYYYMM'),
--             TO_CHAR(h.settlement_date, 'YYYY'),
--             EXTRACT(MONTH FROM h.settlement_date)::INTEGER
--         INTO
--             v_cpnyid,
--             v_perpost,
--             v_year,
--             v_month
--         FROM tr_vpl_settlement h
--         WHERE h.settlement_id = p_docid
--           AND h.cpnyid = p_cpny_id
--         FOR UPDATE;
-- 
--         IF NOT FOUND THEN
--             RAISE EXCEPTION
--                 'Dokumen settlement % dengan company % tidak ditemukan.',
--                 p_docid,
--                 p_cpny_id;
--         END IF;
-- 
--         IF v_perpost IS NULL THEN
--             RAISE EXCEPTION
--                 'Settlement date dokumen % tidak boleh kosong.',
--                 p_docid;
--         END IF;


				SELECT
						h.cpnyid,
						h.settlement_date,
						h.department
				INTO
						v_cpnyid,
						v_settlement_date,
						v_department
				FROM tr_vpl_settlement h
				WHERE h.settlement_id = p_docid
					AND h.cpnyid = p_cpny_id
				FOR UPDATE;

				IF NOT FOUND THEN
						RAISE EXCEPTION
								'Dokumen settlement % dengan company % tidak ditemukan.',
								p_docid,
								p_cpny_id;
				END IF;
				
				/*
				================================================================
				PENENTUAN PERIODE POSTING

				CUSTOMERSERVICE: pakai settlement_date

				Selain CUSTOMERSERVICE: pakai datetime saat SP dijalankan
				================================================================
				*/

				IF UPPER(TRIM(COALESCE(v_department, ''))) = 'CUSTOMERSERVICE' THEN

						IF v_settlement_date IS NULL THEN
								RAISE EXCEPTION
										'Settlement date dokumen % tidak boleh kosong untuk department CUSTOMERSERVICE.',
										p_docid;
						END IF;

						v_perpost := TO_CHAR(v_settlement_date, 'YYYYMM');
						v_year    := TO_CHAR(v_settlement_date, 'YYYY');
						v_month   := EXTRACT(MONTH FROM v_settlement_date)::INTEGER;
						v_post_date := v_settlement_date;

				ELSE

						v_perpost := TO_CHAR(CURRENT_TIMESTAMP, 'YYYYMM');
						v_year    := TO_CHAR(CURRENT_TIMESTAMP, 'YYYY');
						v_month   := EXTRACT(MONTH FROM CURRENT_TIMESTAMP)::INTEGER;
						v_post_date := CURRENT_TIMESTAMP;

				END IF;
				
				/*
				================================================================
				LOOP SETIAP LINE DETAIL USAGE
				================================================================
				*/
					
        FOR r_detail IN
            SELECT
                d.id,
                d.settlement_id,
                d.usage_id,
                d.linenbr,
                d.product_id,
                d.expired_date,
                d.whs_id,
                COALESCE(d.qty_usage, 0) AS qty_usage,
                COALESCE(d.qty_settlement, 0) AS qty_settlement,
                (COALESCE(d.qty_usage, 0) - COALESCE(d.qty_settlement, 0)) AS qty_remain_calc,
                d.qty_remain,
                h.settlement_date
            FROM tr_vpl_settlement_detail d
            INNER JOIN tr_vpl_settlement h
                ON h.settlement_id = d.settlement_id
            WHERE d.settlement_id = p_docid
              AND h.cpnyid = p_cpny_id
            ORDER BY d.linenbr, d.id
            FOR UPDATE OF d
        LOOP
            v_row_count := v_row_count + 1;

            IF r_detail.linenbr IS NULL THEN
                RAISE EXCEPTION
                    'Line number kosong pada detail ID %, dokumen %.',
                    r_detail.id,
                    p_docid;
            END IF;

            IF NULLIF(TRIM(r_detail.usage_id), '') IS NULL THEN
                RAISE EXCEPTION
                    'Usage ID kosong pada dokumen %, line %.',
                    p_docid,
                    r_detail.linenbr;
            END IF;

            IF NULLIF(TRIM(r_detail.product_id), '') IS NULL THEN
                RAISE EXCEPTION
                    'Product ID kosong pada dokumen %, line %.',
                    p_docid,
                    r_detail.linenbr;
            END IF;

            IF NULLIF(TRIM(r_detail.whs_id), '') IS NULL THEN
                RAISE EXCEPTION
                    'Warehouse ID kosong pada dokumen %, line %.',
                    p_docid,
                    r_detail.linenbr;
            END IF;

            IF r_detail.qty_usage <= 0 THEN
                RAISE EXCEPTION
                    'Qty usage harus lebih besar dari 0. Dokumen %, line %, qty usage %.',
                    p_docid,
                    r_detail.linenbr,
                    r_detail.qty_usage;
            END IF;

            IF r_detail.qty_settlement < 0 THEN
                RAISE EXCEPTION
                    'Qty settlement tidak boleh minus. Dokumen %, line %, qty settlement %.',
                    p_docid,
                    r_detail.linenbr,
                    r_detail.qty_settlement;
            END IF;

            IF r_detail.qty_settlement > r_detail.qty_usage THEN
                RAISE EXCEPTION
                    'Qty settlement tidak boleh lebih besar dari qty usage. '
                    'Dokumen %, line %, qty usage %, qty settlement %.',
                    p_docid,
                    r_detail.linenbr,
                    r_detail.qty_usage,
                    r_detail.qty_settlement;
            END IF;

            /*
            ============================================================
            VALIDASI STATUS MASTER PRODUCT & WAREHOUSE (added 2026-08-10, SPM-02 fix)

            Hanya diblokir jika master DITEMUKAN dan berstatus non-aktif.
            Baris master yang tidak ditemukan sama sekali tidak diblokir
            di sini (di luar scope perbaikan ini).
            ============================================================
            */

            IF EXISTS (
                SELECT 1 FROM ms_vpl_product p
                WHERE p.product_id = r_detail.product_id
                  AND p.cpnyid = p_cpny_id
                  AND COALESCE(p.status, '') <> 'A'
            ) THEN
                RAISE EXCEPTION
                    'Product % (company %) berstatus tidak aktif. Dokumen %, line %.',
                    r_detail.product_id, p_cpny_id, p_docid, r_detail.linenbr;
            END IF;

            IF EXISTS (
                SELECT 1 FROM ms_vpl_warehouse w
                WHERE w.whs_id = r_detail.whs_id
                  AND w.cpnyid = p_cpny_id
                  AND COALESCE(w.status, '') <> 'A'
            ) THEN
                RAISE EXCEPTION
                    'Warehouse % (company %) berstatus tidak aktif. Dokumen %, line %.',
                    r_detail.whs_id, p_cpny_id, p_docid, r_detail.linenbr;
            END IF;

            /*
            CROSS-DOCUMENT GUARD (added 2026-08-10, QAVPL-SP SPS-06 gap fix)
            The net-ledger check a few lines below only looks at THIS
            document's own refnbr, so a second Settlement document against
            the same usage line (same usage_id + linenbr) could Submit while
            an earlier, still-active (non-reversed) Settlement document for
            that same line is already crediting stock back — double-crediting
            the remain. Every other doctype in this procedure (VPR/VPT/VPU)
            requires the PRIOR entry for a line to be reversed before a new
            one can Submit; apply the same rule here across documents:
            block Submit if any OTHER settlement document still has a live
            ledger credit for this usage_id + linenbr.
            */
            IF v_activity = 'Submit' THEN

                IF EXISTS (
                    SELECT 1
                    FROM tr_vpl_settlement_detail sd
                    INNER JOIN tr_vpl_ledger l2
                        ON l2.refnbr = sd.settlement_id
                       AND l2.linenbr = sd.linenbr
                       AND l2.product_id = sd.product_id
                       AND l2.expired_date IS NOT DISTINCT FROM sd.expired_date
                       AND l2.whs_id = sd.whs_id
                       AND l2.status = 'A'
                    WHERE sd.usage_id = r_detail.usage_id
                      AND sd.linenbr = r_detail.linenbr
                      AND sd.settlement_id <> p_docid
                    GROUP BY l2.refnbr
                    HAVING SUM(l2.qty) <> 0
                ) THEN
                    RAISE EXCEPTION
                        'Usage % line % sudah mempunyai settlement aktif pada dokumen lain. '
                        'Reject/Revise dokumen tersebut terlebih dahulu sebelum membuat settlement baru untuk line yang sama.',
                        r_detail.usage_id,
                        r_detail.linenbr;
                END IF;

            END IF;

            UPDATE tr_vpl_settlement_detail
            SET
                qty_remain = r_detail.qty_remain_calc,
                updated_user = p_user,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = r_detail.id;

            SELECT COALESCE(SUM(l.qty), 0)
            INTO v_qty
            FROM tr_vpl_ledger l
            WHERE l.refnbr = p_docid
              AND l.cpnyid = p_cpny_id
              AND l.linenbr = r_detail.linenbr
              AND l.product_id = r_detail.product_id
              AND l.expired_date IS NOT DISTINCT FROM r_detail.expired_date
              AND l.whs_id = r_detail.whs_id
              AND l.status = 'A';

            IF v_activity = 'Submit' THEN

                IF v_qty <> 0 THEN
                    RAISE EXCEPTION
                        'Line settlement sudah pernah Submit dan belum dibalikkan. '
                        'Dokumen %, line %, product %, expired date %, warehouse %, net ledger %.',
                        p_docid,
                        r_detail.linenbr,
                        r_detail.product_id,
                        r_detail.expired_date,
                        r_detail.whs_id,
                        v_qty;
                END IF;

                v_qty := r_detail.qty_remain_calc;

            ELSIF v_activity IN ('Reject', 'Revise') THEN

                IF v_qty < 0 THEN
                    RAISE EXCEPTION
                        'Net ledger settlement tidak valid. '
                        'Dokumen %, line %, product %, warehouse %, net ledger %.',
                        p_docid,
                        r_detail.linenbr,
                        r_detail.product_id,
                        r_detail.whs_id,
                        v_qty;
                END IF;

            END IF;

            IF v_qty = 0 THEN
                CONTINUE;
            END IF;

            /*
            ACTIVITY 1 - LEDGER
            */
            INSERT INTO tr_vpl_ledger
            (
                refnbr, refdate, reference_refnbr, cpnyid, postdate, perpost,
                linenbr, product_id, expired_date, whs_id, purpose_id,
                transaction_source, transaction_activity, qty, status,
                created_user, created_at
            )
            VALUES
            (
                p_docid,
                v_post_date,
                r_detail.usage_id,
                p_cpny_id,
                v_post_date,
                v_perpost,
                r_detail.linenbr,
                r_detail.product_id,
                r_detail.expired_date,
                r_detail.whs_id,
                NULL,
                'Settlement',
                v_activity,
                CASE
                    WHEN v_activity = 'Submit' THEN v_qty
                    ELSE v_qty * -1
                END,
                'A',
                p_user,
                CURRENT_TIMESTAMP
            );

            /*
            ACTIVITY 2 - PASTIKAN STOCK BALANCE ADA
            */
            IF NOT EXISTS
            (
                SELECT 1
                FROM ms_vpl_product_bal b
                WHERE b.year = v_year
                  AND b.cpnyid = p_cpny_id
                  AND b.product_id = r_detail.product_id
                  AND b.expired_date IS NOT DISTINCT FROM r_detail.expired_date
                  AND b.whs_id = r_detail.whs_id
            ) THEN
                INSERT INTO ms_vpl_product_bal
                (
                    year, perpost, product_id, expired_date, cpnyid, whs_id, begqty,
                    period01in, period01out, period02in, period02out,
                    period03in, period03out, period04in, period04out,
                    period05in, period05out, period06in, period06out,
                    period07in, period07out, period08in, period08out,
                    period09in, period09out, period10in, period10out,
                    period11in, period11out, period12in, period12out,
                    status, created_user, created_at
                )
                VALUES
                (
                    v_year, v_perpost, r_detail.product_id, r_detail.expired_date,
                    p_cpny_id, r_detail.whs_id, 0,
                    0,0, 0,0, 0,0, 0,0, 0,0, 0,0,
                    0,0, 0,0, 0,0, 0,0, 0,0, 0,0,
                    'A', p_user, CURRENT_TIMESTAMP
                );
            END IF;

            UPDATE ms_vpl_product_bal
            SET
                perpost = v_perpost,

                period01in = COALESCE(period01in, 0) + CASE WHEN v_month = 1 AND v_activity = 'Submit' THEN v_qty ELSE 0 END,
                period01out = COALESCE(period01out, 0) + CASE WHEN v_month = 1 AND v_activity IN ('Reject','Revise') THEN v_qty ELSE 0 END,

                period02in = COALESCE(period02in, 0) + CASE WHEN v_month = 2 AND v_activity = 'Submit' THEN v_qty ELSE 0 END,
                period02out = COALESCE(period02out, 0) + CASE WHEN v_month = 2 AND v_activity IN ('Reject','Revise') THEN v_qty ELSE 0 END,

                period03in = COALESCE(period03in, 0) + CASE WHEN v_month = 3 AND v_activity = 'Submit' THEN v_qty ELSE 0 END,
                period03out = COALESCE(period03out, 0) + CASE WHEN v_month = 3 AND v_activity IN ('Reject','Revise') THEN v_qty ELSE 0 END,

                period04in = COALESCE(period04in, 0) + CASE WHEN v_month = 4 AND v_activity = 'Submit' THEN v_qty ELSE 0 END,
                period04out = COALESCE(period04out, 0) + CASE WHEN v_month = 4 AND v_activity IN ('Reject','Revise') THEN v_qty ELSE 0 END,

                period05in = COALESCE(period05in, 0) + CASE WHEN v_month = 5 AND v_activity = 'Submit' THEN v_qty ELSE 0 END,
                period05out = COALESCE(period05out, 0) + CASE WHEN v_month = 5 AND v_activity IN ('Reject','Revise') THEN v_qty ELSE 0 END,

                period06in = COALESCE(period06in, 0) + CASE WHEN v_month = 6 AND v_activity = 'Submit' THEN v_qty ELSE 0 END,
                period06out = COALESCE(period06out, 0) + CASE WHEN v_month = 6 AND v_activity IN ('Reject','Revise') THEN v_qty ELSE 0 END,

                period07in = COALESCE(period07in, 0) + CASE WHEN v_month = 7 AND v_activity = 'Submit' THEN v_qty ELSE 0 END,
                period07out = COALESCE(period07out, 0) + CASE WHEN v_month = 7 AND v_activity IN ('Reject','Revise') THEN v_qty ELSE 0 END,

                period08in = COALESCE(period08in, 0) + CASE WHEN v_month = 8 AND v_activity = 'Submit' THEN v_qty ELSE 0 END,
                period08out = COALESCE(period08out, 0) + CASE WHEN v_month = 8 AND v_activity IN ('Reject','Revise') THEN v_qty ELSE 0 END,

                period09in = COALESCE(period09in, 0) + CASE WHEN v_month = 9 AND v_activity = 'Submit' THEN v_qty ELSE 0 END,
                period09out = COALESCE(period09out, 0) + CASE WHEN v_month = 9 AND v_activity IN ('Reject','Revise') THEN v_qty ELSE 0 END,

                period10in = COALESCE(period10in, 0) + CASE WHEN v_month = 10 AND v_activity = 'Submit' THEN v_qty ELSE 0 END,
                period10out = COALESCE(period10out, 0) + CASE WHEN v_month = 10 AND v_activity IN ('Reject','Revise') THEN v_qty ELSE 0 END,

                period11in = COALESCE(period11in, 0) + CASE WHEN v_month = 11 AND v_activity = 'Submit' THEN v_qty ELSE 0 END,
                period11out = COALESCE(period11out, 0) + CASE WHEN v_month = 11 AND v_activity IN ('Reject','Revise') THEN v_qty ELSE 0 END,

                period12in = COALESCE(period12in, 0) + CASE WHEN v_month = 12 AND v_activity = 'Submit' THEN v_qty ELSE 0 END,
                period12out = COALESCE(period12out, 0) + CASE WHEN v_month = 12 AND v_activity IN ('Reject','Revise') THEN v_qty ELSE 0 END,

                updated_user = p_user,
                updated_at = CURRENT_TIMESTAMP
            WHERE year = v_year
              AND cpnyid = p_cpny_id
              AND product_id = r_detail.product_id
              AND expired_date IS NOT DISTINCT FROM r_detail.expired_date
              AND whs_id = r_detail.whs_id;

            /*
            ACTIVITY 3 - STOCK QTY
            */
            IF v_activity = 'Submit' THEN

                UPDATE ms_vpl_product_detail
                SET
                    qty_available = COALESCE(qty_available, 0) + v_qty,
                    updated_user = p_user,
                    updated_at = CURRENT_TIMESTAMP
                WHERE cpnyid = p_cpny_id
                  AND product_id = r_detail.product_id
                  AND expired_date IS NOT DISTINCT FROM r_detail.expired_date
                  AND whs_id = r_detail.whs_id;

                IF NOT FOUND THEN
                    INSERT INTO ms_vpl_product_detail
                    (
                        product_id, expired_date, cpnyid, whs_id,
                        qty_available, target_date, status,
                        created_user, created_at
                    )
                    VALUES
                    (
                        r_detail.product_id,
                        r_detail.expired_date,
                        p_cpny_id,
                        r_detail.whs_id,
                        v_qty,
                        NULL,
                        'A',
                        p_user,
                        CURRENT_TIMESTAMP
                    );
                END IF;

            ELSE

                UPDATE ms_vpl_product_detail
                SET
                    qty_available = COALESCE(qty_available, 0) - v_qty,
                    updated_user = p_user,
                    updated_at = CURRENT_TIMESTAMP
                WHERE cpnyid = p_cpny_id
                  AND product_id = r_detail.product_id
                  AND expired_date IS NOT DISTINCT FROM r_detail.expired_date
                  AND whs_id = r_detail.whs_id
                  AND COALESCE(qty_available, 0) >= v_qty;

                IF NOT FOUND THEN
                    RAISE EXCEPTION
                        'Stock tidak mencukupi untuk pembalikan Settlement. '
                        'Dokumen %, line %, product %, expired date %, warehouse %, qty pembalikan %.',
                        p_docid,
                        r_detail.linenbr,
                        r_detail.product_id,
                        r_detail.expired_date,
                        r_detail.whs_id,
                        v_qty;
                END IF;

            END IF;

        END LOOP;

        IF v_row_count = 0 THEN
            RAISE EXCEPTION
                'Dokumen settlement % tidak mempunyai detail.',
                p_docid;
        END IF;

        RAISE NOTICE
            'Proses VPS berhasil. Dokumen %, activity %, jumlah detail %.',
            p_docid,
            v_activity,
            v_row_count;

    ELSE
        RAISE EXCEPTION
            'Doctype % belum tersedia pada sp_process_vpl.',
            p_doctype;
    END IF;

END;
$procedure$
