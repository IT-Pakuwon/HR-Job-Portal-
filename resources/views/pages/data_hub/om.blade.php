<x-app-layout>
    @include('pages.data_hub.styles')
    <div class="dh-page">
        <a href="{{ route('datahub.index') }}" class="dh-back">← Back to Data Hub</a>
        <h1 class="dh-heading">OM Dashboard</h1>
        <p class="dh-description">Pilih mall untuk melihat performa operasional, laporan departemen, dan Kaizen.</p>
        <div class="dh-grid">
            @forelse ($malls as $code => $name)
                <x-data-hub.card :title="$name" :description="'Pantau performa operasional ' . $name . '.'" icon="🏢" :href="route('datahub.om.detail', $code)" />
            @empty
                <div class="dh-empty">
                    <h2>Tidak ada mall yang dapat diakses</h2>
                    <p>Akses Data Hub tersedia, tetapi perusahaan akun belum dipetakan ke mall OM.</p>
                </div>
            @endforelse
        </div>
    </div>
</x-app-layout>
