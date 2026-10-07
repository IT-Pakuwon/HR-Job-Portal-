-- =====================================================================
-- Legal Agreement follow-up letters (Surat 1 / Surat 2) — running numbers.
-- Runs against PGSQL5 (db_das_test). Idempotent.
--
-- One row per letter actually issued, so the PDF can print its number
-- ({seq}/LGL-{company}/{property}/{roman month}/{year}) and a later Surat 2 /
-- escalation email can re-print the earlier letters with the same number.
-- letter_seq runs per company per year.
-- =====================================================================

CREATE TABLE IF NOT EXISTS tr_agreement_letter (
    id                    BIGSERIAL PRIMARY KEY,
    agreement_id          VARCHAR(30)  NOT NULL,
    agreement_step_order  NUMERIC(8,2) NOT NULL DEFAULT 0,
    letter_type           VARCHAR(10)  NOT NULL,   -- SRT1 | SRT2
    cpny_id               VARCHAR(20)  NOT NULL,
    letter_year           SMALLINT     NOT NULL,
    letter_seq            INTEGER      NOT NULL,
    letter_no             VARCHAR(80)  NOT NULL,
    sent_at               TIMESTAMP    NOT NULL,
    created_at            TIMESTAMP    NOT NULL DEFAULT now()
);

-- A reactivation bumps agreement_step_order, so each follow-up cycle gets its
-- own Surat 1 / Surat 2.
CREATE UNIQUE INDEX IF NOT EXISTS ux_tr_agreement_letter_cycle
    ON tr_agreement_letter (agreement_id, agreement_step_order, letter_type);

CREATE UNIQUE INDEX IF NOT EXISTS ux_tr_agreement_letter_seq
    ON tr_agreement_letter (cpny_id, letter_year, letter_seq);
