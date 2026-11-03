<x-app-layout>
<link rel="stylesheet" href="{{ asset('assets/css/vms/document.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/vms/vendor.css').'?v='.filemtime(public_path('assets/css/vms/vendor.css')) }}">
<link rel="stylesheet" href="{{ asset('assets/css/vms/vendor-directory.css').'?v='.filemtime(public_path('assets/css/vms/vendor-directory.css')) }}">
<link rel="stylesheet" href="{{ asset('assets/css/vms/purchase-order.css').'?v='.filemtime(public_path('assets/css/vms/purchase-order.css')) }}">
@include('pages.vms.invoice-register.content')
<dialog id="vms-order-modal" aria-labelledby="vms-order-title"><header><h2 id="vms-order-title">Invoice Register</h2><button type="button" data-invoice-close aria-label="Tutup">&times;</button></header><div data-order-content aria-live="polite"></div><footer><button type="button" data-invoice-close>Tutup</button></footer></dialog>
@push('scripts')<script src="{{ asset('assets/js/vms/invoice-register.js').'?v='.filemtime(public_path('assets/js/vms/invoice-register.js')) }}" defer></script>@endpush
</x-app-layout>
