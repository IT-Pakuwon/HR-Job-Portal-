/*
 * DashPanel: the engine behind <x-panel.shell> (resources/views/components/panel).
 * One instance per panel element, found through [data-dp] inside its root, so panels never clash on ids
 * and any number can be on one page. A panel type only says how a row is drawn:
 *   DashPanel.types['it-ticket'] = { icon, badgeBg, title(row), link(row), subtitle(row), date(row), status(row), statusValue(row), searchFields(row) }
 */
(function () {
    const REFRESH_SECONDS = window.dashRefreshSeconds || 20;

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

    // ── Zoom panel: meetings by email invitation; recordings are read from Zoom only when a row asks for them ──
    const zoomRec = {}; // docid -> { open, loading, files, passcode, message }; kept here so the 20s refresh doesn't close it
    types['zoom'] = {
        icon: '🎥', badgeBg: 'bg-sky-100 dark:bg-sky-900/30',
        title: (r) => r.title, link: null,
        statusValue: (r) => (r.ended ? 'ENDED' : 'UPCOMING'),
        status: (r) => (r.ended ? pill('Ended', GREY) : pill('Upcoming', 'bg-sky-100 text-sky-700 border-sky-200')),
        // one compact line per meeting: details under the title, buttons beside the status badge
        subtitle: (r) => joinDot([r.date, `${r.start} – ${r.end}`, r.location, r.organizer, r.docid]),
        date: () => null,
        searchFields: (r) => [r.title, r.docid, r.organizer, r.location],
        buttons: (r) => {
            const rec = zoomRec[r.docid];
            let h = '';
            if (r.join_url && !r.ended) {
                h += `<a href="${esc(r.join_url)}" target="_blank" rel="noopener noreferrer" class="rounded-full bg-sky-500 px-3 py-0.5 text-[11px] font-semibold text-white transition hover:bg-sky-600">Join Zoom</a>`;
            }
            if (r.can_record) {
                const label = rec && rec.loading ? 'Loading…' : (rec && rec.open ? 'Hide recording' : 'Recording');
                h += `<button type="button" data-zoom-rec="${esc(r.docid)}" class="rounded-full bg-white px-3 py-0.5 text-[11px] font-semibold text-sky-700 ring-1 ring-sky-300 transition hover:bg-sky-100 dark:bg-transparent dark:text-sky-300 dark:ring-sky-500/40">${label}</button>`;
            }
            return h;
        },
        actions: (r) => {
            const rec = zoomRec[r.docid];
            let h = '';
            if (rec && rec.open && !rec.loading) {
                h += '<div class="mt-2 space-y-1">';
                if (rec.message) h += `<p class="text-[11px] text-slate-500 dark:text-slate-400">${esc(rec.message)}</p>`;
                (rec.files || []).forEach((f) => {
                    h += `<a href="${esc(f.url)}" target="_blank" rel="noopener noreferrer" class="mr-1 inline-flex items-center gap-1 rounded-md bg-white px-2 py-0.5 text-[11px] font-semibold text-sky-700 ring-1 ring-sky-200 hover:bg-sky-100 dark:bg-sky-500/10 dark:text-sky-300 dark:ring-sky-500/30">${esc(f.label)}<span class="font-normal text-slate-400">${esc(f.type)}</span></a>`;
                });
                if (rec.passcode && (rec.files || []).length) h += `<p class="text-[11px] text-slate-500 dark:text-slate-400">Passcode: <span class="font-mono font-semibold">${esc(rec.passcode)}</span></p>`;
                h += '</div>';
            }
            return h;
        },
        bind: (root, list, redraw) => {
            list.addEventListener('click', (e) => {
                const btn = e.target.closest('[data-zoom-rec]');
                if (!btn) return;
                const id = btn.dataset.zoomRec;
                const rec = zoomRec[id];
                if (rec && rec.files !== undefined) { rec.open = !rec.open; redraw(); return; } // already fetched: just toggle
                zoomRec[id] = { open: true, loading: true };
                redraw();
                fetch(`/todo/meetings/${encodeURIComponent(id)}/recording`, { headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' } })
                    .then((r) => r.json().catch(() => ({})).then((j) => ({ ok: r.ok, j })))
                    .then(({ ok, j }) => { zoomRec[id] = { open: true, files: j.files || [], passcode: j.passcode || null, message: ok ? (j.message || null) : (j.message || 'Could not load the recording.') }; })
                    .catch(() => { zoomRec[id] = { open: true, files: [], message: 'Could not load the recording.' }; })
                    .then(redraw);
            });
        },
    };

    // ── Plaud panel: recordings of the Plaud account(s) mapped to this dashboard for the user's company.
    // Clicking a row opens its summary in a modal that only the X or the Close button can dismiss. ──
    const plaudRows = {};  // meeting id -> row, so a click can show its header without asking the server again
    const plaudSum = {};   // meeting id -> { summaries, message } once fetched (an empty/pending answer is asked again)
    // Summaries are markdown; headings, bold/italic, bullets, quotes and rules are enough here. Everything is escaped first.
    const mdLite = (text) => esc(text).split('\n').map((line) => {
        const t = line.trim();
        if (!t) return '<div class="h-1.5"></div>';
        if (/^-{3,}$/.test(t)) return '<hr class="my-2 border-slate-200 dark:border-slate-700">';
        const inline = (s) => s.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>').replace(/(^|[\s(])\*([^*\s][^*]*?)\*(?=[\s).,;:!?]|$)/g, '$1<em>$2</em>');
        const q = t.match(/^&gt;\s?(.*)$/); // a "> quote" line (escaped by esc())
        if (q) return `<div class="border-l-2 border-slate-300 pl-3 text-slate-500 dark:border-slate-600 dark:text-slate-400">${inline(q[1])}</div>`;
        const h = t.match(/^#{1,6}\s+(.*)$/);
        if (h) return `<div class="mt-3 text-sm font-bold text-slate-800 dark:text-slate-100">${inline(h[1])}</div>`;
        const li = t.match(/^[-*•]\s+(.*)$/);
        if (li) return `<div class="flex gap-1.5 pl-1"><span class="text-slate-400">•</span><span>${inline(li[1])}</span></div>`;
        return `<div>${inline(t)}</div>`;
    }).join('');

    // One modal for the whole page, created on first use. Deliberately NOT closable by Esc or a click outside:
    // only the X in the header and the Close button in the footer.
    let plaudModal = null;
    function plaudModalEl() {
        if (plaudModal) return plaudModal;
        const el = document.createElement('div');
        el.className = 'fixed inset-0 z-[100] hidden items-center justify-center bg-gray-900/60 p-4 backdrop-blur-sm';
        el.setAttribute('role', 'dialog');
        el.setAttribute('aria-modal', 'true');
        el.innerHTML = `
            <div class="flex max-h-[90vh] w-full max-w-4xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-slate-800">
                <div class="flex items-start gap-3 border-b border-slate-200 px-5 py-4 dark:border-slate-700">
                    <span class="mt-0.5 flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-violet-100 text-lg dark:bg-violet-900/30">🎙️</span>
                    <div class="min-w-0 flex-1">
                        <h3 data-pm="title" class="text-base font-bold leading-snug text-slate-800 dark:text-slate-100"></h3>
                        <p data-pm="meta" class="mt-0.5 text-xs text-slate-500 dark:text-slate-400"></p>
                    </div>
                    <button type="button" data-pm="x" title="Close" aria-label="Close"
                        class="shrink-0 rounded-lg p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-700">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
                <div data-pm="body" class="min-h-0 flex-1 space-y-3 overflow-y-auto px-5 py-4 text-sm leading-relaxed text-slate-600 dark:text-slate-300"></div>
                <div class="flex justify-end border-t border-slate-200 bg-slate-50 px-5 py-3 dark:border-slate-700 dark:bg-slate-900/40">
                    <button type="button" data-pm="close"
                        class="rounded-lg bg-black px-5 py-1.5 text-xs font-semibold text-white hover:bg-gray-900 dark:bg-zinc-700 dark:hover:bg-zinc-600">Close</button>
                </div>
            </div>`;
        document.body.appendChild(el);
        const hide = () => { el.classList.add('hidden'); el.classList.remove('flex'); document.body.style.overflow = ''; };
        el.querySelector('[data-pm="x"]').addEventListener('click', hide);
        el.querySelector('[data-pm="close"]').addEventListener('click', hide);
        el.__hide = hide;
        plaudModal = el;
        return el;
    }

    function plaudModalBody(id) {
        const s = plaudSum[id];
        if (!s) return '<p class="py-10 text-center text-slate-400">Loading the summary…</p>';
        let h = '';
        if (s.message) h += `<p class="rounded-lg bg-amber-50 px-3 py-2 text-xs text-amber-700 dark:bg-amber-500/10 dark:text-amber-300">${esc(s.message)}</p>`;
        (s.summaries || []).forEach((m) => {
            if (m.title) h += `<div class="text-sm font-bold text-slate-800 dark:text-slate-100">${esc(m.title)}</div>`;
            if (m.content) h += `<div>${mdLite(m.content)}</div>`;
            if ((m.action_items || []).length) {
                h += '<div class="mt-3 text-sm font-bold text-slate-800 dark:text-slate-100">Action items</div>';
                m.action_items.forEach((a) => { h += `<div class="flex gap-1.5 pl-1"><span class="text-slate-400">☐</span><span>${esc(typeof a === 'string' ? a : (a.text || a.title || a.task || JSON.stringify(a)))}</span></div>`; });
            }
        });
        return h;
    }

    function plaudOpen(id, dash) {
        const row = plaudRows[id] || {};
        const el = plaudModalEl();
        el.querySelector('[data-pm="title"]').textContent = row.title || 'Recording';
        el.querySelector('[data-pm="meta"]').textContent = joinDot([row.date, row.duration, row.account]);
        const body = el.querySelector('[data-pm="body"]');
        const show = () => { body.innerHTML = plaudModalBody(id); };
        el.classList.remove('hidden'); el.classList.add('flex');
        document.body.style.overflow = 'hidden';
        body.scrollTop = 0;

        const cached = plaudSum[id];
        if (cached && cached.summaries && cached.summaries.length) { show(); return; }
        delete plaudSum[id];
        show();
        fetch(`/plaud/summary/${encodeURIComponent(id)}?dash=${encodeURIComponent(dash)}`, { headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' } })
            .then((r) => r.json().catch(() => ({})).then((j) => ({ ok: r.ok, j })))
            .then(({ ok, j }) => { plaudSum[id] = { summaries: j.summaries || [], message: ok ? (j.message || null) : (j.message || 'Could not load the summary.') }; })
            .catch(() => { plaudSum[id] = { summaries: [], message: 'Could not load the summary.' }; })
            .then(show);
    }

    types['plaud'] = {
        icon: '🎙️', badgeBg: 'bg-violet-100 dark:bg-violet-900/30',
        title: (r) => r.title, link: null,
        statusValue: (r) => (r.has_summary ? 'READY' : 'PENDING'),
        status: (r) => (r.has_summary ? pill('Summary', 'bg-emerald-100 text-emerald-700 border-emerald-200') : pill('Pending', 'bg-amber-100 text-amber-700 border-amber-200')),
        subtitle: (r) => joinDot([r.date, r.duration, r.account]),
        date: () => null,
        searchFields: (r) => [r.title, r.account, r.date],
        // the whole row is the button
        rowAttr: (r) => { plaudRows[r.id] = r; return `data-plaud-row="${esc(r.id)}" role="button" tabindex="0" title="Open the summary" class="cursor-pointer transition-colors hover:bg-slate-50 dark:hover:bg-slate-700/30"`; },
        bind: (root, list) => {
            // the dashboard code travels in the panel's own URL (?dash=), so the summary call uses the same scope
            const dash = new URL(root.dataset.dpUrl, location.origin).searchParams.get('dash') || '';
            const open = (e) => { const row = e.target.closest('[data-plaud-row]'); if (row) plaudOpen(row.dataset.plaudRow, dash); };
            list.addEventListener('click', open);
            list.addEventListener('keydown', (e) => { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); open(e); } });
        },
    };

    function renderCard(cfg, row) {
        let status = cfg.status ? cfg.status(row) : '';
        if (cfg.buttons) status = `<span class="flex shrink-0 items-center gap-2">${cfg.buttons(row)}${status}</span>`;
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
                    ${cfg.actions ? cfg.actions(row) : ''}
                </div>`;
        return href
            ? `<a href="${esc(href)}" target="_blank" rel="noopener noreferrer" class="-mx-4 flex items-start gap-3 px-4 py-3 transition-colors hover:bg-slate-50 dark:hover:bg-slate-700/30">${inner}</a>`
            : (cfg.rowAttr
                ? `<div ${cfg.rowAttr(row).replace('class="', 'class="-mx-4 flex items-start gap-3 px-4 py-3 ')}>${inner}</div>`
                : `<div class="-mx-4 flex items-start gap-3 px-4 py-3">${inner}</div>`);
    }

    function mount(root) {
        if (!root || root.__dp) return;
        const cfg = types[root.dataset.dpType];
        if (!cfg) { console.error('DashPanel: unknown type', root.dataset.dpType); return; }
        root.__dp = true;

        const $ = (name) => root.querySelector(`[data-dp="${name}"]`);
        const el = { list: $('list'), empty: $('empty'), info: $('info'), prev: $('prev'), next: $('next'), search: $('search'),
            status: $('status'), pagesize: $('pagesize'), refresh: $('refresh'), openall: $('openall'), countdown: $('countdown') };

        let rows = [], page = 0, pageSize = parseInt(el.pagesize.value, 10) || 10, hovering = false, pending = null, ctrl = null, remaining = REFRESH_SECONDS;

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
        if (cfg.bind) cfg.bind(root, el.list, draw);
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
