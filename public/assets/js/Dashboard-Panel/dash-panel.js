/*
 * DashPanel: the engine behind <x-panel.shell> (resources/views/components/panel).
 * One instance per panel element, found through [data-dp] inside its root, so panels never clash on ids
 * and any number can be on one page. A panel type only says how a row is drawn:
 *   DashPanel.types['it-ticket'] = { icon, badgeBg, title(row), link(row), subtitle(row), date(row), status(row), statusValue(row), searchFields(row) }
 */
(function () {
    const REFRESH_SECONDS = 60;

    const esc = (v) => String(v ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

    const pill = (label, cls) =>
        `<span class="inline-flex shrink-0 items-center rounded-full border px-2.5 py-0.5 text-[11px] font-semibold whitespace-nowrap ${cls}">${esc(label)}</span>`;
    const GREY = 'bg-slate-100 text-slate-700 border-slate-200';

    // A badge from a { value: [label, classes] } map; unknown values fall back to grey.
    const badgeFrom = (map) => (value) => {
        const key = String(value ?? '').toUpperCase();
        const [label, cls] = map[key] || [key || '-', GREY];
        return pill(label, cls);
    };

    const joinDot = (parts) => parts.filter(Boolean).join(' • ');

    const types = {};
    window.DashPanel = { types, esc, pill, badgeFrom, joinDot, mount };

    // ── IT dashboard tabs ──
    types['it-ticket'] = {
        icon: '🎫', badgeBg: 'bg-blue-100 dark:bg-blue-900/30',
        title: (r) => r.ticketid,
        link: (r) => `/showticket/${r.eid}`,
        subtitle: (r) => joinDot([r.cpny_id, r.department_id, r.ticket_category_name, r.pic_ticket ? `PIC: ${r.pic_ticket}` : null]),
        date: (r) => r.ticketdate,
        statusValue: (r) => (r.status_pekerjaan || '').toUpperCase(),
        status: (r) => badgeFrom({
            CREATED: ['CREATED', GREY],
            RESPONSE: ['RESPONSE', 'bg-blue-100 text-blue-700 border-blue-200'],
            PROCESS: ['PROCESS', 'bg-amber-100 text-amber-700 border-amber-200'],
            PENDING: ['PENDING', 'bg-orange-100 text-orange-700 border-orange-200'],
            ENVISION: ['ENVISION', 'bg-violet-100 text-violet-700 border-violet-200'],
            'ENVISION CHECKED / SOLVED': ['ENVISION CHECKED / SOLVED', 'bg-purple-100 text-purple-700 border-purple-200'],
            COMPLETED: ['COMPLETED', 'bg-emerald-100 text-emerald-700 border-emerald-200'],
            TRANSFER: ['TRANSFER', 'bg-cyan-100 text-cyan-700 border-cyan-200'],
            REOPEN: ['REOPEN', 'bg-red-100 text-red-700 border-red-200'],
            CANCEL: ['CANCEL', 'bg-slate-200 text-slate-700 border-slate-300'],
        })(r.status_pekerjaan),
        searchFields: (r) => [r.ticketid, r.issue_summary, r.user_peminta, r.cpny_id, r.ticket_category_name, r.pic_ticket],
    };

    types['it-access'] = {
        icon: '🔐', badgeBg: 'bg-orange-100 dark:bg-orange-900/30',
        title: (r) => r.docid,
        link: (r) => `/showaccessrequest/${r.eid}`,
        subtitle: (r) => joinDot([r.user_peminta, r.access_type, r.user_assign ? `PIC: ${r.user_assign}` : null]),
        date: (r) => r.created_at,
        statusValue: (r) => (r.status || '').toUpperCase(),
        status: (r) => badgeFrom({ C: ['Waiting for Process', 'bg-blue-100 text-blue-700 border-blue-200'] })(r.status),
        searchFields: (r) => [r.docid, r.user_peminta, r.keperluan, r.user_assign],
    };

    types['it-recommendation'] = {
        icon: '💡', badgeBg: 'bg-violet-100 dark:bg-violet-900/30',
        title: (r) => r.docid,
        link: (r) => `/showitrecommendation/${r.eid}`,
        subtitle: (r) => joinDot([r.cpny_id, r.department_id, r.recommend_type, r.recommend_pic ? `PIC: ${r.recommend_pic}` : null]),
        date: (r) => r.itrecommend_date,
        statusValue: (r) => (r.status || '').toUpperCase(),
        status: (r) => badgeFrom({
            W: ['Waiting for IT', 'bg-blue-100 text-blue-700 border-blue-200'],
            I: ['Waiting Revision', 'bg-violet-100 text-violet-700 border-violet-200'],
        })(r.status),
        searchFields: (r) => [r.docid, r.user_peminta, r.recommend_type, r.recommend_pic],
    };

    // ── shared badges ──
    const TICKET_STATUS = {
        CREATED: ['CREATED', GREY],
        RESPONSE: ['RESPONSE', 'bg-blue-100 text-blue-700 border-blue-200'],
        APPROVED: ['APPROVED', 'bg-teal-100 text-teal-700 border-teal-200'],
        PROCESS: ['PROCESS', 'bg-amber-100 text-amber-700 border-amber-200'],
        REVISED: ['REVISED', 'bg-yellow-100 text-yellow-700 border-yellow-200'],
        PENDING: ['PENDING', 'bg-orange-100 text-orange-700 border-orange-200'],
        COMPLETED: ['COMPLETED', 'bg-emerald-100 text-emerald-700 border-emerald-200'],
        TRANSFER: ['TRANSFER', 'bg-cyan-100 text-cyan-700 border-cyan-200'],
        REOPEN: ['REOPEN', 'bg-red-100 text-red-700 border-red-200'],
        REJECTED: ['REJECTED', 'bg-rose-100 text-rose-700 border-rose-200'],
        CANCEL: ['CANCEL', 'bg-slate-200 text-slate-700 border-slate-300'],
    };
    const docStatus = badgeFrom({
        P: ['Waiting Approval', 'bg-amber-100 text-amber-700 border-amber-200'],
        C: ['Completed', 'bg-emerald-100 text-emerald-700 border-emerald-200'],
        R: ['Rejected', 'bg-red-100 text-red-700 border-red-200'],
        D: ['Revised', 'bg-orange-100 text-orange-700 border-orange-200'],
        X: ['Cancelled', 'bg-slate-200 text-slate-700 border-slate-300'],
    });
    const ticketStatus = badgeFrom(TICKET_STATUS);
    const BA_TYPES = { ENGSUPPORTTICKET: 'Engineering', BSSUPPORTTICKET: 'Building Service', FOSUPPORTTICKET: 'Fit Out',
        BA_ENG: 'Berita Acara ENG', BA_BS: 'Request For Approval', BA_FO: 'Document Approval' };
    const docLink = (r) => `${r.url}/${r.eid}`;

    // ── GA dashboard tabs (the private-note button of the GA page is not part of the panel) ──
    types['ga-voucher-taxi'] = {
        icon: '🚕', badgeBg: 'bg-amber-100 dark:bg-amber-900/30',
        title: (r) => r.docid, link: docLink,
        fields: (r) => [
            { label: 'Date', value: r.voucher_date }, { label: 'Requester', value: r.user_peminta }, { label: 'Owner', value: r.user_peminta_expense },
            { label: 'From', value: r.origin }, { label: 'To', value: r.destination }, { label: 'Purpose', value: r.purpose_descr },
        ],
        searchFields: (r) => [r.docid, r.user_peminta, r.origin, r.destination, r.purpose_descr],
    };
    types['ga-booking-car'] = {
        icon: '🚗', badgeBg: 'bg-blue-100 dark:bg-blue-900/30',
        title: (r) => r.docid, link: docLink,
        fields: (r) => [
            { label: 'Date', value: r.booking_date }, { label: 'Requester', value: r.user_peminta }, { label: 'Driver', value: r.driver },
            { label: 'Purpose', value: r.purpose_descr },
        ],
        searchFields: (r) => [r.docid, r.user_peminta, r.driver, r.purpose_descr],
    };
    types['ga-parking'] = {
        icon: '🅿️', badgeBg: 'bg-violet-100 dark:bg-violet-900/30',
        title: (r) => r.docid, link: docLink,
        status: (r) => badgeFrom({
            P: ['PROCESS', 'bg-amber-100 text-amber-700 border-amber-200'], H: ['HOLD', 'bg-red-100 text-red-700 border-red-200'],
            C: ['COMPLETED', 'bg-emerald-100 text-emerald-700 border-emerald-200'], X: ['CANCELLED', 'bg-slate-200 text-slate-700 border-slate-300'],
        })(r.status),
        fields: (r) => [
            { label: 'Date', value: r.parking_regist_date }, { label: 'Requester', value: r.user_peminta }, { label: 'Type', value: r.parking_type },
            { label: 'Worker', value: r.worker_type }, { label: 'Perpost', value: r.perpost },
        ],
        searchFields: (r) => [r.docid, r.user_peminta, r.parking_type, r.worker_type],
    };

    // ── HR dashboard tabs ──
    types['hr-prf'] = {
        icon: '📄', badgeBg: 'bg-orange-100 dark:bg-orange-900/30',
        title: (r) => r.docid, link: docLink, status: (r) => docStatus(r.status),
        fields: (r) => [
            { label: 'Date', value: r.date }, { label: 'Company', value: r.cpnyid }, { label: 'Dept', value: r.departementid },
            { label: 'Position', value: r.job_title }, { label: 'Required', value: r.required }, { label: 'Actual', value: r.actual },
        ],
        searchFields: (r) => [r.docid, r.cpnyid, r.departementid, r.job_title],
    };
    types['hr-applicant'] = {
        icon: '👤', badgeBg: 'bg-violet-100 dark:bg-violet-900/30',
        title: (r) => r.fullname || r.docid, link: docLink, status: (r) => docStatus(r.status),
        fields: (r) => [
            { label: 'Doc', value: r.docid }, { label: 'Position', value: r.job_title }, { label: 'Company', value: r.cpnyid },
            { label: 'Apply Date', value: r.apply_date }, { label: 'Stage', value: r.apply_step },
        ],
        searchFields: (r) => [r.docid, r.fullname, r.job_title, r.cpnyid],
    };
    types['hr-self-register'] = {
        icon: '👥', badgeBg: 'bg-cyan-100 dark:bg-cyan-900/30',
        title: (r) => r.fullname || r.docid, link: docLink, status: (r) => docStatus(r.status),
        fields: (r) => [
            { label: 'Doc', value: r.docid }, { label: 'Position', value: r.job_title }, { label: 'Company', value: r.cpnyid },
            { label: 'Apply Date', value: r.apply_date },
        ],
        searchFields: (r) => [r.docid, r.fullname, r.job_title, r.cpnyid],
    };

    // ── Operational dashboard tab ──
    types['opr-workorder'] = {
        icon: '🔧', badgeBg: 'bg-orange-100 dark:bg-orange-900/30',
        title: (r) => r.woid, link: docLink,
        status: (r) => badgeFrom({ P: ['ON PROGRESS', 'bg-amber-100 text-amber-700 border-amber-200'], H: ['HOLD', 'bg-red-100 text-red-700 border-red-200'] })(r.status_pekerjaan),
        fields: (r) => [
            { label: 'Date', value: r.wodate }, { label: 'Type', value: r.wotype }, { label: 'Requester', value: r.picrequester },
            { label: 'PIC', value: r.pic_wo }, { label: 'Purpose', value: r.keperluan },
        ],
        searchFields: (r) => [r.woid, r.wotype, r.picrequester, r.pic_wo, r.keperluan],
    };

    // ── Recruitment dashboard tabs ──
    types['recruit-applicant'] = {
        icon: '👤', badgeBg: 'bg-violet-100 dark:bg-violet-900/30',
        title: (r) => r.fullname || r.docid, link: docLink,
        fields: (r) => [
            { label: 'Doc', value: r.docid }, { label: 'Position', value: r.job_title }, { label: 'Company', value: r.cpnyid },
            { label: 'Apply Date', value: r.apply_date }, { label: 'Stage', value: r.apply_step },
        ],
        searchFields: (r) => [r.docid, r.fullname, r.job_title, r.cpnyid],
    };
    types['recruit-self-register'] = {
        icon: '👥', badgeBg: 'bg-cyan-100 dark:bg-cyan-900/30',
        title: (r) => r.fullname || r.docid, link: docLink,
        fields: (r) => [
            { label: 'Doc', value: r.docid }, { label: 'Position', value: r.job_title }, { label: 'Company', value: r.cpnyid },
            { label: 'Division', value: r.division }, { label: 'Department', value: r.department }, { label: 'Apply Date', value: r.apply_date },
        ],
        searchFields: (r) => [r.docid, r.fullname, r.job_title, r.cpnyid, r.division, r.department],
    };

    // ── Corporate Teknik dashboard tabs ──
    const corptekTicket = (title) => ({
        icon: '🎫', badgeBg: 'bg-blue-100 dark:bg-blue-900/30',
        title, link: (r) => `/showoprtekticket/${r.eid}`,
        date: (r) => r.ticketdate,
        statusValue: (r) => (r.status_pekerjaan || '').toUpperCase(),
        status: (r) => ticketStatus((r.status_pekerjaan || '-').toUpperCase()),
    });
    types['corptek-ticket'] = Object.assign(corptekTicket((r) => r.ticketid), {
        subtitle: (r) => joinDot([BA_TYPES[r.ticket_type] || r.ticket_type, r.cpny_id, r.location_name, r.pic_ticket ? `PIC: ${r.pic_ticket}` : null]),
        searchFields: (r) => [r.ticketid, r.issue_summary, r.user_peminta, r.cpny_id, r.ticket_category_name, r.pic_ticket],
    });
    types['corptek-berita-acara'] = Object.assign(corptekTicket((r) => r.ba_auto_number || r.ticketid), {
        icon: '📄', badgeBg: 'bg-violet-100 dark:bg-violet-900/30',
        subtitle: (r) => joinDot([BA_TYPES[r.ticket_type] || r.ticket_type, r.cpny_id, [r.location_name, r.sub_location_name].filter(Boolean).join(' / '), r.pic_ticket ? `PIC: ${r.pic_ticket}` : null]),
        searchFields: (r) => [r.ticketid, r.ba_auto_number, r.issue_summary, r.user_peminta, r.cpny_id, r.pic_ticket],
    });

    // ── money / date helpers ──
    const idr = (v) => (v === null || v === undefined || v === '' ? null : Number(v).toLocaleString('id-ID', { maximumFractionDigits: 0 }));
    const field = (label, value) => ({ label, value });

    // ── Cost Control dashboard tabs ──
    types['cost-po'] = {
        icon: '📦', badgeBg: 'bg-orange-100 dark:bg-orange-900/30',
        title: (r) => r.order_no, link: null,
        fields: (r) => [field('Date', r.order_date), field('Company', r.cpny_id), field('Dept', r.department_id), field('Requester', r.user_peminta), field('Purchaser', r.purchaser)],
        searchFields: (r) => [r.order_no, r.cpny_id, r.department_id, r.user_peminta, r.purchaser],
    };
    types['cost-issue'] = {
        icon: '🚚', badgeBg: 'bg-rose-100 dark:bg-rose-900/30',
        title: (r) => r.issue_id, link: null,
        fields: (r) => [field('Date', r.issue_date), field('Company', r.cpny_id), field('Dept', r.department_id), field('Requester', r.user_peminta), field('Keeper', r.keeper)],
        searchFields: (r) => [r.issue_id, r.cpny_id, r.department_id, r.user_peminta, r.keeper],
    };
    types['cost-budget'] = {
        icon: '💰', badgeBg: 'bg-blue-100 dark:bg-blue-900/30',
        title: (r) => r.account_id, link: null,
        fields: (r) => [field('Company', r.cpny_id), field('BU', r.business_unit_id), field('Dept', r.department_fin_id), field('Activity', r.activity_descr), field('Remaining', idr(r.remaining_budget) ?? '—')],
        searchFields: (r) => [r.cpny_id, r.business_unit_id, r.department_fin_id, r.account_id, r.activity_descr],
    };
    types['cost-imbudget'] = {
        icon: '📊', badgeBg: 'bg-violet-100 dark:bg-violet-900/30',
        title: (r) => r.imbudgetid, link: (r) => `/showimbudgets/${r.eid}`,
        status: (r) => badgeFrom({
            H: ['On Hold', 'bg-amber-100 text-amber-700 border-amber-200'], P: ['On Progress', 'bg-blue-100 text-blue-700 border-blue-200'],
            C: ['Completed', 'bg-emerald-100 text-emerald-700 border-emerald-200'],
        })(r.status),
        fields: (r) => [field('Date', r.imbudgetdate), field('Company', r.cpny_id), field('Dept', r.department_id), field('Requester', r.user_peminta), field('CS Ref', r.csid), field('Requested', idr(r.total_budget_requested) ?? '—')],
        searchFields: (r) => [r.imbudgetid, r.cpny_id, r.department_id, r.user_peminta, r.csid],
    };

    // ── Voucher / product dashboards (Cost Control, VP Collection / Promotion / Loyalty) ──
    const expiredType = () => ({
        icon: '⏰', badgeBg: 'bg-amber-100 dark:bg-amber-900/30',
        title: (r) => `${r.product_id} - ${r.product_name || ''}`, link: (r) => `${r.url}/${r.hid}/view`,
        status: (r) => pill(`${r.bucket} · ${r.days_left}d`, r.bucket === 'H-30' ? 'bg-red-100 text-red-700 border-red-200' : 'bg-amber-100 text-amber-700 border-amber-200'),
        fields: (r) => [field('Type', r.product_type_label), field('Expired Date', r.expired_date), field('Company', r.cpnyid), field('Warehouse', r.whs_id), field('Qty Available', r.qty_pickable)],
        searchFields: (r) => [r.product_id, r.product_name, r.cpnyid, r.whs_id, r.product_type_label],
    });
    const settlementType = (bg) => ({
        icon: '🧾', badgeBg: bg,
        title: (r) => r.usage_id, link: docLink,
        fields: (r) => [field('Usage Date', r.usage_date), field('Event Date', r.event_date), field('Requester', r.user_peminta), field('Company', r.cpnyid), field('Dept', r.department), field('Remark', r.usage_remark)],
        searchFields: (r) => [r.usage_id, r.user_peminta, r.cpnyid, r.department, r.usage_remark],
    });
    types['cost-expired'] = expiredType();
    types['cost-waiting-settlement'] = settlementType('bg-fuchsia-100 dark:bg-fuchsia-900/30');
    types['vpcoll-expired'] = expiredType();
    types['vpcoll-settlement'] = settlementType('bg-violet-100 dark:bg-violet-900/30');
    types['vpprmtn-expired'] = expiredType();
    types['vpprmtn-settlement'] = settlementType('bg-violet-100 dark:bg-violet-900/30');
    types['vployalty-expired'] = expiredType();

    // ── Finance / Treasury dashboards (same four documents, different stage) ──
    const rfcaType = () => ({
        icon: '💰', badgeBg: 'bg-amber-100 dark:bg-amber-900/30',
        title: (r) => r.rfcaid, link: docLink,
        fields: (r) => [field('Date', r.rfcadate), field('Company', r.cpny_id), field('Dept', r.department_id), field('PO', r.ponbr), field('Vendor', r.vendorname), field('Type', r.rfca_type), field('By', r.created_by)],
        searchFields: (r) => [r.rfcaid, r.cpny_id, r.department_id, r.ponbr, r.vendorname],
    });
    const calrPurchType = () => ({
        icon: '🔄', badgeBg: 'bg-emerald-100 dark:bg-emerald-900/30',
        title: (r) => r.calrid, link: docLink,
        fields: (r) => [field('Date', r.calrdate), field('Company', r.cpny_id), field('Dept', r.department_id), field('RFCA Ref', r.rfcaid), field('Vendor', r.vendorname), field('Amount', idr(r.calr_amount)), field('By', r.created_by)],
        searchFields: (r) => [r.calrid, r.cpny_id, r.department_id, r.rfcaid, r.vendorname],
    });
    const rfpNonPurchType = (received) => ({
        icon: '📄', badgeBg: 'bg-violet-100 dark:bg-violet-900/30',
        title: (r) => r.rfpnonpurchaseid, link: docLink,
        fields: (r) => [field('Date', r.rfpnonpurchasedate), field('Company', r.cpny_id), field('Dept', r.department_id), field('Type', r.rfpnonpurchase_type), field('Desc', r.keperluan), field('Amount', idr(r.amountrequestpayment))]
            .concat(received ? [field('Received By', r.userreceive), field('Received Date', r.receivedate)] : []),
        searchFields: (r) => [r.rfpnonpurchaseid, r.cpny_id, r.department_id, r.keperluan],
    });
    const calrNonPurchType = (received) => ({
        icon: '📑', badgeBg: 'bg-rose-100 dark:bg-rose-900/30',
        title: (r) => r.calrnonpurchaseid, link: docLink,
        fields: (r) => [field('Date', r.calrnonpurchasedate), field('Company', r.cpny_id), field('Dept', r.department_id), field('RFP Ref', r.rfpnonpurchaseid), field('Desc', r.keperluan), field('Settlement', idr(r.amountsettlement))]
            .concat(received ? [field('Received By', r.userreceive), field('Received Date', r.receivedate)] : []),
        searchFields: (r) => [r.calrnonpurchaseid, r.cpny_id, r.department_id, r.keperluan],
    });
    types['fin-rfca-purchase'] = rfcaType();
    types['fin-calr-purchase'] = calrPurchType();
    types['fin-rfp-nonpurch'] = rfpNonPurchType(false);
    types['fin-calr-nonpurch'] = calrNonPurchType(false);
    types['treas-rfca-purchase'] = Object.assign(rfcaType(), { icon: '💳' });
    types['treas-calr-purchase'] = Object.assign(calrPurchType(), { icon: '🔁' });
    types['treas-rfp-nonpurch'] = rfpNonPurchType(true);
    types['treas-calr-nonpurch'] = calrNonPurchType(true);

    // ── Warehouse dashboard tabs ──
    const solomon = (r) => badgeFrom({ P: ['Waiting for Processing', 'bg-amber-100 text-amber-700 border-amber-200'], C: ['COMPLETED', 'bg-emerald-100 text-emerald-700 border-emerald-200'] })(r);
    types['wh-sppb'] = {
        icon: '📦', badgeBg: 'bg-amber-100 dark:bg-amber-900/30',
        title: (r) => r.sppbid, link: docLink, status: () => solomon('P'),
        fields: (r) => [field('Date', r.sppbdate), field('Company', r.cpny_id), field('Dept', r.department_id), field('Type', r.requesttype_name), field('Desc', r.keperluan)],
        searchFields: (r) => [r.sppbid, r.cpny_id, r.department_id, r.keperluan],
    };
    types['wh-po-solomon'] = {
        icon: '🛒', badgeBg: 'bg-blue-100 dark:bg-blue-900/30',
        title: (r) => r.order_no, link: null, status: (r) => solomon(r.stage_status),
        fields: (r) => [field('Company', r.cpny_id), field('Order Date', r.order_date), field('Dept', r.department_id), field('Last Update', r.last_update)],
        searchFields: (r) => [r.order_no, r.cpny_id, r.department_id],
    };
    types['wh-grn-solomon'] = {
        icon: '📥', badgeBg: 'bg-violet-100 dark:bg-violet-900/30',
        title: (r) => r.grn_no, link: null, status: (r) => solomon(r.stage_status),
        fields: (r) => [field('Company', r.cpny_id), field('GRN Date', r.grn_date), field('PO No', r.order_no), field('Last Update', r.last_update)],
        searchFields: (r) => [r.grn_no, r.cpny_id, r.order_no],
    };
    types['wh-issue-solomon'] = {
        icon: '📤', badgeBg: 'bg-rose-100 dark:bg-rose-900/30',
        title: (r) => r.issue_id, link: null, status: (r) => solomon(r.stage_status),
        fields: (r) => [field('Company', r.cpny_id), field('Issue Date', r.issue_date), field('Reference', r.reference_no), field('Dept', r.department_id), field('Requester', r.user_peminta), field('Last Update', r.last_update)],
        searchFields: (r) => [r.issue_id, r.cpny_id, r.reference_no, r.user_peminta],
    };

    // ── Purchasing dashboard tabs ──
    types['purch-cs'] = {
        icon: '📄', badgeBg: 'bg-amber-100 dark:bg-amber-900/30',
        title: (r) => r.docid, link: docLink,
        // the filter groups Draft (H, D) vs On Progress (P)
        statusValue: (r) => (r.status === 'P' ? 'PROGRESS' : 'DRAFT'),
        status: (r) => badgeFrom({ H: ['Draft', 'bg-slate-100 text-slate-700 border-slate-300'], D: ['Revisi', 'bg-orange-100 text-orange-700 border-orange-200'], P: ['On Progress', 'bg-blue-100 text-blue-700 border-blue-200'] })(r.status),
        fields: (r) => [field('Date', r.csdate), field('Company', r.cpny_id), field('Dept', r.department_id), field('Purpose', r.keperluan), field('By', r.created_by)],
        searchFields: (r) => [r.docid, r.cpny_id, r.department_id, r.keperluan],
    };
    types['purch-po-unsend'] = {
        icon: '📦', badgeBg: 'bg-violet-100 dark:bg-violet-900/30',
        title: (r) => r.docid, link: docLink,
        statusValue: (r) => r.po_status_key,
        status: (r) => pill(r.po_status_label, r.po_status_cls),
        fields: (r) => [field('Date', r.podate), field('Company', r.cpny_id), field('Type', r.potype), field('Vendor', r.vendorname), field('Purpose', r.keperluan), field('Total', idr(r.grandtotalamt)), field('By', r.created_by)],
        searchFields: (r) => [r.docid, r.cpny_id, r.vendorname, r.keperluan],
    };

    function renderCard(cfg, row) {
        const status = cfg.status ? cfg.status(row) : '';
        const href = cfg.link ? cfg.link(row) : null;
        let body;
        if (cfg.fields) {
            // label/value grid, e.g. a voucher or a work order
            const grid = cfg.fields(row).filter((f) => f.value)
                .map((f) => `<div class="truncate"><span class="text-slate-400 dark:text-slate-500">${esc(f.label)}:</span> ${esc(f.value)}</div>`).join('');
            body = `<div class="mt-1 grid grid-cols-2 gap-x-3 gap-y-0.5 text-xs text-slate-500 dark:text-slate-400 sm:grid-cols-3">${grid}</div>`;
        } else {
            const subtitle = cfg.subtitle(row);
            const when = cfg.date(row);
            body = `<div class="mt-0.5 flex flex-wrap items-center gap-1 text-xs text-slate-400 dark:text-slate-500">
                        ${subtitle ? `<span class="truncate">${esc(subtitle)}</span>` : ''}
                        ${subtitle && when ? '<span>•</span>' : ''}
                        ${when ? `<span>${esc(when)}</span>` : ''}
                    </div>`;
        }
        const inner = `
                <div class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-lg ${cfg.badgeBg} text-base">${cfg.icon}</div>
                <div class="min-w-0 flex-1">
                    <div class="flex items-center justify-between gap-2">
                        <span class="truncate text-sm font-semibold text-slate-800 dark:text-slate-100">${esc(cfg.title(row) || '-')}</span>
                        ${status}
                    </div>
                    ${body}
                </div>`;
        return href
            ? `<a href="${esc(href)}" target="_blank" rel="noopener noreferrer" class="-mx-4 flex items-start gap-3 px-4 py-3 transition-colors hover:bg-slate-50 dark:hover:bg-slate-700/30">${inner}</a>`
            : `<div class="-mx-4 flex items-start gap-3 px-4 py-3">${inner}</div>`;
    }

    function mount(root) {
        if (!root || root.__dp) return;
        const cfg = types[root.dataset.dpType];
        if (!cfg) { console.error('DashPanel: unknown type', root.dataset.dpType); return; }
        root.__dp = true;

        const $ = (name) => root.querySelector(`[data-dp="${name}"]`);
        const el = { list: $('list'), empty: $('empty'), info: $('info'), prev: $('prev'), next: $('next'), search: $('search'),
            status: $('status'), pagesize: $('pagesize'), refresh: $('refresh'), openall: $('openall'), countdown: $('countdown') };

        let rows = [], page = 0, pageSize = 10, hovering = false, pending = null, ctrl = null, remaining = REFRESH_SECONDS;

        const filtered = () => {
            const term = (el.search.value || '').trim().toLowerCase();
            const status = el.status ? el.status.value : 'ALL';
            return rows.filter((r) =>
                (status === 'ALL' || (cfg.statusValue ? cfg.statusValue(r) : '') === status)
                && (!term || cfg.searchFields(r).some((f) => String(f ?? '').toLowerCase().includes(term))));
        };

        function draw() {
            const list = filtered();
            const pages = Math.max(1, Math.ceil(list.length / pageSize));
            page = Math.min(page, pages - 1);
            const start = page * pageSize;
            const slice = list.slice(start, start + pageSize);

            el.list.innerHTML = slice.map((r) => renderCard(cfg, r)).join('');
            el.empty.classList.toggle('hidden', slice.length > 0);
            el.info.textContent = `Showing ${list.length ? start + 1 : 0} to ${Math.min(start + pageSize, list.length)} of ${list.length} entries`;
            el.prev.disabled = page === 0;
            el.next.disabled = page >= pages - 1;
        }

        function load(resetPage = true) {
            if (ctrl) ctrl.abort();
            ctrl = new AbortController();
            fetch(root.dataset.dpUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' }, signal: ctrl.signal })
                .then((r) => { if (!r.ok) throw new Error(`HTTP ${r.status}`); return r.json(); })
                .then((res) => {
                    const data = res.data || [];
                    // Don't swap the list while the pointer is on it: it would pull a link out from under a click.
                    if (hovering) { pending = data; return; }
                    rows = data;
                    if (resetPage) page = 0;
                    draw();
                    remaining = REFRESH_SECONDS;
                })
                .catch((e) => { if (e.name !== 'AbortError') console.error(e); });
        }

        el.list.addEventListener('mouseenter', () => { hovering = true; });
        el.list.addEventListener('mouseleave', () => {
            hovering = false;
            if (pending) { rows = pending; pending = null; draw(); remaining = REFRESH_SECONDS; }
        });
        el.search.addEventListener('input', () => { page = 0; draw(); });
        if (el.status) el.status.addEventListener('change', () => { page = 0; draw(); });
        el.pagesize.addEventListener('change', () => { pageSize = parseInt(el.pagesize.value, 10) || 10; page = 0; draw(); });
        el.prev.addEventListener('click', () => { if (page > 0) { page--; draw(); } });
        el.next.addEventListener('click', () => { page++; draw(); });
        el.refresh.addEventListener('click', () => load(false));
        el.openall.addEventListener('click', () => filtered().forEach((r) => { const h = cfg.link && cfg.link(r); if (h) window.open(h, '_blank'); }));

        const fmt = (n) => `${String(Math.floor(n / 60)).padStart(2, '0')}:${String(n % 60).padStart(2, '0')}`;
        const tick = setInterval(() => {
            if (!root.isConnected) { clearInterval(tick); if (ctrl) ctrl.abort(); return; }
            if (document.hidden || hovering) return;
            remaining--;
            if (el.countdown) el.countdown.textContent = fmt(Math.max(remaining, 0));
            if (remaining <= 0) { remaining = REFRESH_SECONDS; load(false); }
        }, 1000);

        if (el.countdown) el.countdown.textContent = fmt(remaining);
        load();
    }
})();
