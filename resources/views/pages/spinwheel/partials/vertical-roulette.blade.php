{{-- Shared vertical roulette visual, included by both the operator and audience spinwheel views. --}}

<style>
    .roulette-reel {
        position: relative;
        width: 100%;
        flex: 1 1 280px;
        height: 60vh;
        min-height: 340px;
        overflow: hidden;
        border-radius: 1rem;
        border: 2px solid rgba(255, 255, 255, .15);
        background: linear-gradient(180deg, rgba(139, 92, 246, .15), rgba(236, 72, 153, .15));
        box-shadow: 0 0 40px rgba(139, 92, 246, .35);
    }

    .roulette-reel::before,
    .roulette-reel::after {
        content: '';
        position: absolute;
        left: 0;
        right: 0;
        height: 45%;
        pointer-events: none;
        z-index: 2;
    }

    .roulette-reel::before {
        top: 0;
        background: linear-gradient(180deg, #0b0a1a, transparent);
    }

    .roulette-reel::after {
        bottom: 0;
        background: linear-gradient(0deg, #0b0a1a, transparent);
    }

    .roulette-reel-track {
        position: absolute;
        left: 0;
        right: 0;
        top: 0;
        transform: translateY(0);
        transition: transform 4.5s cubic-bezier(0.12, 0.85, 0.2, 1);
    }

    .roulette-reel-row {
        height: 12vh;
        min-height: 68px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        padding: 0 14px;
        font-weight: 700;
        color: #f1f5f9;
        font-size: 1.35rem;
        line-height: 1.2;
    }

    .roulette-marker {
        position: absolute;
        left: 0;
        right: 0;
        top: 50%;
        transform: translateY(-50%);
        height: 12vh;
        min-height: 68px;
        border-top: 2px solid #f472b6;
        border-bottom: 2px solid #f472b6;
        background: rgba(236, 72, 153, .1);
        box-shadow: 0 0 20px rgba(244, 114, 182, .4) inset;
        z-index: 1;
        pointer-events: none;
    }
</style>

<div class="flex w-full flex-1 min-h-0 flex-wrap items-center justify-center gap-4" id="rouletteReels"></div>

<script>
    const ROULETTE_VISIBLE_ROWS = 5;
    const ROULETTE_PADDING_ROWS = 22;

    // reel/row height is derived straight from the viewport (matching the vh +
    // min-height values in the CSS above) instead of measuring the rendered DOM
    // element — this stays correct even mid-fullscreen-transition, when a
    // flex-chain measurement can momentarily read a stale/zero height
    function currentRouletteRowHeight() {
        const reelHeight = Math.max(window.innerHeight * 0.6, 340);
        return reelHeight / ROULETTE_VISIBLE_ROWS;
    }

    function escapeHtml(value) {
        return $('<div>').text(value ?? '').html();
    }

    function candidateLabel(candidate, combo) {

        const name = escapeHtml(candidate.customer_name);
        const company = escapeHtml(candidate.company_name);
        const refNbr = escapeHtml(candidate.ref_nbr);

        if (combo === 'name_refnbr') {
            return `${name}${refNbr ? ' — ' + refNbr : ''}`;
        }

        return `${name}${company ? ' — ' + company : ''}`;

    }

    function pickRouletteLabel(pool, combo, avoidLabel) {

        let label;
        let attempts = 0;

        do {
            const sample = pool[Math.floor(Math.random() * pool.length)];
            label = candidateLabel(sample, combo);
            attempts++;
        } while (label === avoidLabel && pool.length > 1 && attempts < 10);

        return label;

    }

    function buildRouletteReel(candidate, combo, sampleNames) {

        const pool = (sampleNames && sampleNames.length) ? sampleNames : [{
            customer_name: '???',
            company_name: '',
            ref_nbr: ''
        }];
        const rows = [];
        let lastLabel = null;

        for (let i = 0; i < ROULETTE_PADDING_ROWS; i++) {
            const label = pickRouletteLabel(pool, combo, lastLabel);
            rows.push(label);
            lastLabel = label;
        }
        rows.push(candidateLabel(candidate, combo));

        const track = $('<div class="roulette-reel-track"></div>');
        rows.forEach(label => {
            track.append(`<div class="roulette-reel-row">${label}</div>`);
        });

        const reel = $(`
            <div class="roulette-reel" data-ref-nbr="${escapeHtml(candidate.ref_nbr)}"></div>
        `);

        reel.append(`<div class="roulette-marker"></div>`);
        reel.append(track);

        return { reel, track, targetIndex: rows.length - 1 };

    }

    function spinVerticalRoulette(candidates, combo, durationMs = 4500) {

        const wrap = $('#rouletteReels');
        wrap.html('');

        const built = candidates.map(candidate => buildRouletteReel(candidate, combo, window.sampleNames));

        built.forEach(({ reel }) => wrap.append(reel));

        requestAnimationFrame(() => {

            const rowHeight = currentRouletteRowHeight();
            const centerOffset = (ROULETTE_VISIBLE_ROWS - 1) / 2 * rowHeight;

            built.forEach(({ track, targetIndex }, i) => {

                const translateY = centerOffset - (targetIndex * rowHeight);

                track.css('transition-duration', (durationMs + i * 300) + 'ms');
                track.css('transform', `translateY(${translateY}px)`);

            });
        });

    }
</script>
