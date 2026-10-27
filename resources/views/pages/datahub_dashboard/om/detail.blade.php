<x-app-layout>
@include('pages.datahub_dashboard.om.partials.helpers')

@push('head')
<link rel="stylesheet" href="{{ asset('assets/css/datahub/om-dashboard.css') }}?v={{ filemtime(public_path('assets/css/datahub/om-dashboard.css')) }}">
@endpush

{{-- Everything below lives in .om-dash; its stylesheet is scoped to it so the app header/sidebar are untouched. --}}
<div class="om-dash">
<div class="wrap">
@include('pages.datahub_dashboard.om.partials.header')
@include('pages.datahub_dashboard.om.tabs.overview')
@include('pages.datahub_dashboard.om.tabs.department')
@include('pages.datahub_dashboard.om.tabs.kaizen')

  <footer>
    <span>Data source: IFCA, PG Card, APP.Pakuwon, Isort, Parking Vendor, Valet System, Fit Out ME</span>
    <span id="lastUpdated"></span>
  </footer>
</div>
</div>

<script src="{{ asset('vendor/chartjs/chart.umd.min.js') }}"></script>

@php
  $assetInventoryJs = $assetInventory ?? [
      'total_good' => 0,
      'total_bad' => 0,
      'total_qty' => 0,
      'departments' => [],
      'items' => [],
  ];
@endphp

<script>
// Wrapped so these top-level names can't collide with the app layout's own globals.
(function () {
@include('pages.datahub_dashboard.om.scripts.core')
@include('pages.datahub_dashboard.om.scripts.overview-charts')
@include('pages.datahub_dashboard.om.scripts.department-charts')
@include('pages.datahub_dashboard.om.scripts.kaizen')
@include('pages.datahub_dashboard.om.scripts.loaders')
@include('pages.datahub_dashboard.om.scripts.department-loaders')
@include('pages.datahub_dashboard.om.scripts.boot')
})();
</script>
</x-app-layout>
