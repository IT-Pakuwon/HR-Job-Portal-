<x-app-layout>
    @include('pages.datahub_dashboard.styles')
    <div class="dh-page">
        <header class="dh-hero">
            <div class="dh-hero-main">
                <span class="dh-hero-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" /></svg>
                </span>
                <div>
                    <h1 class="dh-hero-title">Data Hub</h1>
                    <p class="dh-hero-sub">Pusat akses dashboard Mall, Sport, Loyalty dan HRD dalam satu tempat.</p>
                </div>
            </div>
            <nav class="dh-hero-nav" aria-label="Jump to section">
                <a href="#dh-mall-heading">Mall</a>
                <a href="#dh-sport-heading">Sport</a>
                <a href="#dh-loyalty-heading">Loyalty</a>
                <a href="#dh-hrd-heading">HRD</a>
            </nav>
        </header>
        <section class="dh-section" aria-labelledby="dh-mall-heading">
            <h2 id="dh-mall-heading" class="dh-section-heading">Mall</h2>
            <div class="dh-grid">
                <x-datahub.card title="GM Dashboard" description="Dashboard General Manager untuk monitoring performa mall." icon="👨‍💼" :href="route('datahub-dashboard.category', 'gm')" />
                <x-datahub.card title="OM Dashboard" description="Dashboard Operations Manager untuk monitoring operasional mall." icon="👷" :href="route('datahub-dashboard.om.index')" />
                <x-datahub.card title="Director & Owner Dashboard" description="Dashboard performa mall untuk Director dan Owner." icon="📈" :href="route('datahub-dashboard.category', 'director-owner')" />
            </div>
        </section>
        <section class="dh-section" aria-labelledby="dh-sport-heading">
            <h2 id="dh-sport-heading" class="dh-section-heading">Sport</h2>
            <div class="dh-grid">
                <x-datahub.card title="Tennis Dashboard" description="Dashboard sport dari Power BI." icon="🎾" badge="POWER BI" :href="$sportLinks['tennis'] ?? null" :external="true" />
                <x-datahub.card title="Padel Dashboard" description="Dashboard sport dari Power BI." icon="🎾" badge="POWER BI" :href="$sportLinks['padel'] ?? null" :external="true" />
                <x-datahub.card title="Asia Racket Dashboard" description="Dashboard sport dari Power BI." icon="🎾" badge="POWER BI" :href="$sportLinks['asia_racket'] ?? null" :external="true" />
            </div>
        </section>
        <section class="dh-section" aria-labelledby="dh-loyalty-heading">
            <h2 id="dh-loyalty-heading" class="dh-section-heading">Loyalty</h2>
            <div class="dh-grid">
                <x-datahub.card title="Dashboard PGCard" description="Dashboard loyalty PGCard dari Power BI." icon="💳" badge="POWER BI" :href="$loyaltyLinks['pgcard'] ?? null" :external="true" />
                <x-datahub.card title="Dashboard STYW 2026" description="Dashboard STYW tahun 2026 dari Power BI." icon="📈" badge="POWER BI" :href="$loyaltyLinks['styw_2026'] ?? null" :external="true" />
                <x-datahub.card title="Dashboard STYW 2025" description="Dashboard STYW tahun 2025 dari Power BI." icon="📊" badge="POWER BI" :href="$loyaltyLinks['styw_2025'] ?? null" :external="true" />
                <x-datahub.card title="Dashboard CNY HKTB 2026" description="Dashboard CNY HKTB 2026 dari Power BI." icon="🧧" badge="POWER BI" :href="$loyaltyLinks['cny_hktb_2026'] ?? null" :external="true" />
                <x-datahub.card title="Dashboard PGCard STYW 2026 vs 2025" description="Dashboard comparison STYW 2026 vs 2025 dari Power BI." icon="📊" badge="POWER BI" :href="$loyaltyLinks['pgcard_styw_comparison'] ?? null" :external="true" />
            </div>
        </section>
        <section class="dh-section" aria-labelledby="dh-hrd-heading">
            <h2 id="dh-hrd-heading" class="dh-section-heading">HRD</h2>
            <div class="dh-grid">
                <x-datahub.card title="Job Portal" description="Portal rekrutmen dan informasi lowongan pekerjaan." icon="💼" :href="$hrdLinks['job_portal'] ?? null" :external="true" />
            </div>
        </section>
    </div>
</x-app-layout>