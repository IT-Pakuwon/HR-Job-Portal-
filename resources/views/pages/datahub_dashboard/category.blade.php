<x-app-layout>
    @include('pages.datahub_dashboard.styles')
    <div class="dh-page">
        <a href="{{ route('datahub-dashboard.index') }}" class="dh-back">← Back to Dashboard Portal</a>
        <h1 class="dh-heading">{{ $title }}</h1>
        <p class="dh-description">{{ $description }}</p>
        @if ($category === 'mall-performance')
            <div class="dh-grid">
                <x-datahub.card :title="'Director & Owner Dashboard'" description="Dashboard performa mall untuk Director dan Owner." icon="📈" :href="route('datahub-dashboard.category', 'director-owner')" />
                <x-datahub.card title="GM Dashboard" description="Dashboard General Manager untuk monitoring performa mall." icon="👨‍💼" :href="route('datahub-dashboard.category', 'gm')" />
                <x-datahub.card title="OM Dashboard" description="Dashboard Operations Manager untuk monitoring operasional mall." icon="👷" :href="route('datahub-dashboard.om.index')" />
            </div>
        @elseif ($category === 'loyalty')
            <div class="dh-grid">
                <x-datahub.card title="Dashboard PGCard" description="Dashboard loyalty PGCard dari Power BI." icon="💳" badge="POWER BI" :href="$loyaltyLinks['pgcard'] ?? null" :external="true" />
                <x-datahub.card title="Dashboard STYW 2026" description="Dashboard STYW tahun 2026 dari Power BI." icon="📈" badge="POWER BI" :href="$loyaltyLinks['styw_2026'] ?? null" :external="true" />
                <x-datahub.card title="Dashboard STYW 2025" description="Dashboard STYW tahun 2025 dari Power BI." icon="📊" badge="POWER BI" :href="$loyaltyLinks['styw_2025'] ?? null" :external="true" />
                <x-datahub.card title="Dashboard CNY HKTB 2026" description="Dashboard CNY HKTB 2026 dari Power BI." icon="🧧" badge="POWER BI" :href="$loyaltyLinks['cny_hktb_2026'] ?? null" :external="true" />
                <x-datahub.card title="Dashboard PGCard STYW 2026 vs 2025" description="Dashboard comparison STYW 2026 vs 2025 dari Power BI." icon="📊" badge="POWER BI" :href="$loyaltyLinks['pgcard_styw_comparison'] ?? null" :external="true" />
            </div>
        @elseif ($category === 'hrd')
            <div class="dh-grid">
                <x-datahub.card title="Job Portal" description="Portal rekrutmen dan informasi lowongan pekerjaan." icon="💼" :href="$hrdLinks['job_portal'] ?? null" :external="true" />
            </div>
        @elseif ($category === 'sport')
            <div class="dh-grid">
                <x-datahub.card title="Padel Dashboard" description="Dashboard sport dari Power BI." icon="🎾" badge="POWER BI" :href="$sportLinks['padel'] ?? null" :external="true" />
                <x-datahub.card title="Tennis Dashboard" description="Dashboard sport dari Power BI." icon="🎾" badge="POWER BI" :href="$sportLinks['tennis'] ?? null" :external="true" />
                <x-datahub.card title="Asia Racket Dashboard" description="Dashboard sport dari Power BI." icon="🎾" badge="POWER BI" :href="$sportLinks['asia_racket'] ?? null" :external="true" />
            </div>
        @else
            <div class="dh-empty">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Dashboard belum tersedia</h2>
                <p class="mt-2 text-gray-500 dark:text-gray-400">Belum ada laporan yang terhubung untuk kategori ini.</p>
            </div>
        @endif
    </div>
</x-app-layout>
