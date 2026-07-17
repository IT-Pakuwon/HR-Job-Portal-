<?php

/*
|--------------------------------------------------------------------------
| Approval Doctype Registry
|--------------------------------------------------------------------------
|
| Maps an aprv_doctype code (as stored on tr_approval.aprv_doctype) to the
| header document model that owns that transaction, so the "Manage
| Approval" admin tool can sync the header's status when an approval is
| rolled back. The DB connection is not stored here — it's read at
| runtime from the model itself via getConnectionName(), since header
| models span multiple connections (pgsql, pgsql3, pgsql5).
|
| Deliberately NOT included:
| - 'TOK' (EngTicket): reject doesn't set status='R' on the header (it
|   resets to 'P' / status_pekerjaan='CREATED' instead), so there is no
|   "rejected" header state to roll back from. tr_approval rows for TOK
|   are still searchable/editable/transferable, just not rollback-able.
| - 'AGD' (Agenda): its tr_approval rows are written at creation time but
|   are not the authoritative approval state — AgendaController's reject
|   flow only mutates the legacy T_approval/trx_approval table. Rolling
|   back an Agenda row here would not affect the real document and would
|   be misleading.
| - 'BQ', 'IR': unmapped/dead — BQ has a single experimental ms_approval
|   rule, IR has no controller that emits it.
| - News/ProjectTask/Manpower/StrukturOrg/ChangeSto/Career: never write to
|   tr_approval at all (separate legacy engine), so they don't need an
|   exclusion here — they simply never appear in search results.
|
*/

return [
    'RB'  => ['model' => \App\Models\TrSPB::class,               'refnbr_col' => 'spbid',             'label' => 'SPB'],
    'PB'  => ['model' => \App\Models\TrSPPB::class,              'refnbr_col' => 'sppbid',            'label' => 'SPPB'],
    'PJ'  => ['model' => \App\Models\TrSPPJ::class,              'refnbr_col' => 'sppjid',            'label' => 'SPPJ'],
    'PK'  => ['model' => \App\Models\TrSPPK::class,              'refnbr_col' => 'sppkid',            'label' => 'SPPK'],
    'PT'  => ['model' => \App\Models\TrSPPT::class,              'refnbr_col' => 'spptid',            'label' => 'SPPT'],
    'BA'  => ['model' => \App\Models\TrBast::class,              'refnbr_col' => 'bastid',            'label' => 'BAST'],
    'BD'  => ['model' => \App\Models\Budget::class,              'refnbr_col' => 'budget_id',         'label' => 'Budget'],
    'CA'  => ['model' => \App\Models\TrCalr::class,              'refnbr_col' => 'calrid',            'label' => 'CALR'],
    'CAR' => ['model' => \App\Models\TrCalrNonPurch::class,      'refnbr_col' => 'calrnonpurchaseid', 'label' => 'CALR Non Purchase'],
    'CS'  => ['model' => \App\Models\TrCS::class,                'refnbr_col' => 'csid',              'label' => 'Canvass'],
    'IM'  => ['model' => \App\Models\TrIMBudget::class,          'refnbr_col' => 'imbudgetid',        'label' => 'IM Budget'],
    'IMR' => ['model' => \App\Models\TrImbudgetNonPurch::class,  'refnbr_col' => 'imnonpurchaseid',   'label' => 'IM Budget Non Purchase'],
    'IS'  => ['model' => \App\Models\TrIssue::class,             'refnbr_col' => 'issueid',           'label' => 'Issue'],
    'SR'  => ['model' => \App\Models\TrItemRequest::class,       'refnbr_col' => 'irid',              'label' => 'Item Request'],
    'ITR' => ['model' => \App\Models\TrItrecommend::class,       'refnbr_col' => 'docid',             'label' => 'IT Recommendation'],
    'PKR' => ['model' => \App\Models\TrParkingRegistration::class, 'refnbr_col' => 'docid',           'label' => 'Parking Registration'],
    'GR'  => ['model' => \App\Models\TrReceipt::class,           'refnbr_col' => 'receiptnbr',        'label' => 'Receipt'],
    'RP'  => ['model' => \App\Models\TrRfp::class,               'refnbr_col' => 'rfp_id',            'label' => 'RFP'],
    'RFP' => ['model' => \App\Models\TrRfpNonPurch::class,       'refnbr_col' => 'rfpnonpurchaseid',  'label' => 'RFP Non Purchase'],
    'RCA' => ['model' => \App\Models\TrRfpNonPurch::class,       'refnbr_col' => 'rfpnonpurchaseid',  'label' => 'RCA Non Purchase'],
    'VCR' => ['model' => \App\Models\TrVoucherTaxi::class,       'refnbr_col' => 'docid',             'label' => 'Voucher Taxi'],
    'VPR' => ['model' => \App\Models\TrxVplReceive::class,       'refnbr_col' => 'receive_id',        'label' => 'VPL Receive'],
    'VPT' => ['model' => \App\Models\TrxVplTransfer::class,      'refnbr_col' => 'transfer_id',       'label' => 'VPL Transfer'],
    'VPU' => ['model' => \App\Models\TrxVplUsage::class,         'refnbr_col' => 'usage_id',          'label' => 'VPL Usage'],
    'WO'  => ['model' => \App\Models\TrWO::class,                'refnbr_col' => 'woid',              'label' => 'Work Order'],
    'ACR' => ['model' => \App\Models\TrAccess::class,            'refnbr_col' => 'docid',             'label' => 'Access Request'],
    'BCR' => ['model' => \App\Models\TrBookingCar::class,        'refnbr_col' => 'docid',             'label' => 'Booking Car'],
    'PRF' => ['model' => \App\Models\Personnel::class,           'refnbr_col' => 'docid',             'label' => 'Personnel'],
];
