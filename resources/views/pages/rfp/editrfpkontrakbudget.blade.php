@php
    $formMode = 'edit';
    $submitUrl = route('rfp.kontrak-budget.update', ['hash' => $hash]);
@endphp

@include('pages.rfp.createrfpkontrakbudget')
