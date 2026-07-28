function pgtrekDashboard(initial) {
    return {
        filters: {
            start: initial.start,
            end: initial.end,
            vendor: initial.vendor,
            site: initial.site,
        },
        loading: false,
        error: null,
        dateOpen: false,
        activePreset: 'last-7-days',
        pointCompletion: { rows: [], overall_pct: null },
        timeImplement: { rows: [], overall_pct: null },
        personnel: { active_track_user: null, non_track_user: null, total_beacon_user: null },
        absentDiscipline: { work_days_target: null, work_days_actual: null, work_days_pct: null },
        alertPoint: { groups: [], total_raised: 0 },
        alertRows: [],

        performanceTiers: [
            { label: 'Fully Compliant with Standard', range: '>90%' },
            { label: 'Substantially Compliant with Standard', range: '80% - 90%' },
            { label: 'Moderately Compliant with Standard', range: '70% - 80%' },
            { label: 'Minimum Standard Performance', range: '40% - 70%' },
            { label: 'Below Standard Performance', range: '25% - 40%' },
            { label: 'Unacceptable Performance', range: '<25%' },
        ],

        get scoring() {
            const BOBOT = { absent: 10, point: 30, time: 30, alert: 30 };
            const AGREEMENT_POINTS = { 'Supportive Alert': 0.2, 'Technical Alert': 0.2, 'Neutral Alert': 0.2, 'Negligence Alert': -5 };

            const absentPct = this.absentDiscipline.work_days_pct ?? 0;
            const pointPct = this.pointCompletion.overall_pct ?? 0;
            const timePct = this.timeImplement.overall_pct ?? 0;

            let alertContribution = 0;
            (this.alertPoint.groups || []).forEach(g => {
                alertContribution += g.qty * (AGREEMENT_POINTS[g.aspect] ?? 0);
            });
            const alertPct = Math.min(100, 100 + alertContribution);

            const sections = [
                { label: 'Absent Dicipline', pct: absentPct, bobot: BOBOT.absent },
                { label: 'Beacon Tracking Performance - Track Point Activities Completion', pct: pointPct, bobot: BOBOT.point },
                { label: 'Beacon Tracking Performance - Track Time Implement (Minutes)', pct: timePct, bobot: BOBOT.time },
                { label: 'Alert Point', pct: alertPct, bobot: BOBOT.alert },
            ].map(s => ({ ...s, contribution: Math.round(s.pct * s.bobot) / 100 }));

            const result = Math.round(sections.reduce((sum, s) => sum + s.contribution, 0) * 100) / 100;

            let performance = 'Fully Compliant with Standard';
            if (result <= 25) performance = 'Unacceptable Performance';
            else if (result <= 40) performance = 'Below Standard Performance';
            else if (result <= 70) performance = 'Minimum Standard Performance';
            else if (result <= 80) performance = 'Moderately Compliant with Standard';
            else if (result <= 90) performance = 'Substantially Compliant with Standard';

            return { sections, result, performance };
        },

        presets: [
            { id: 'today', label: 'Today' },
            { id: 'this-week', label: 'This Week' },
            { id: 'last-7-days', label: 'Last 7 Days' },
            { id: 'this-month', label: 'This Month' },
            { id: 'last-month', label: 'Last Month' },
            { id: 'this-year', label: 'This Year' },
        ],

        get dateLabel() {
            const found = this.presets.find(p => p.id === this.activePreset);
            if (found) return found.label;
            return this.filters.start === this.filters.end
                ? this.filters.start
                : this.filters.start + ' – ' + this.filters.end;
        },

        get siteLabel() {
            if (!this.filters.site) return 'All Locations';
            const opt = document.querySelector(`option[value="${this.filters.site}"]`);
            return opt ? opt.textContent : this.filters.site;
        },

        get exportPdfUrl() {
            const params = new URLSearchParams({
                start: this.filters.start,
                end: this.filters.end,
                vendor: this.filters.vendor,
                site: this.filters.site,
            });
            const route = document.querySelector('meta[name="export-pdf-route"]')?.content
                || '/pgtrek/export/pdf';
            return `${route}?${params.toString()}`;
        },

        closeAllPanels() {
            this.dateOpen = false;
        },

        fmt(d) {
            return d.toISOString().slice(0, 10);
        },

        applyPreset(id) {
            const now = new Date();
            let start = new Date(now);
            let end = new Date(now);

            switch (id) {
                case 'today':
                    break;
                case 'this-week': {
                    const dow = (now.getDay() + 6) % 7;
                    start.setDate(now.getDate() - dow);
                    end = new Date(start);
                    end.setDate(start.getDate() + 6);
                    break;
                }
                case 'last-7-days':
                    start.setDate(now.getDate() - 6);
                    break;
                case 'this-month':
                    start = new Date(now.getFullYear(), now.getMonth(), 1);
                    end = new Date(now.getFullYear(), now.getMonth() + 1, 0);
                    break;
                case 'last-month':
                    start = new Date(now.getFullYear(), now.getMonth() - 1, 1);
                    end = new Date(now.getFullYear(), now.getMonth(), 0);
                    break;
                case 'this-year':
                    start = new Date(now.getFullYear(), 0, 1);
                    end = new Date(now.getFullYear(), 11, 31);
                    break;
            }

            this.filters.start = this.fmt(start);
            this.filters.end = this.fmt(end);
            this.activePreset = id;
            this.dateOpen = false;
            this.load();
        },

        async load() {
            this.loading = true;
            this.error = null;

            const params = new URLSearchParams({
                start: this.filters.start,
                end: this.filters.end,
                vendor: this.filters.vendor,
                site: this.filters.site,
            });

            const beaconRoute = document.querySelector('meta[name="beacon-performance-route"]')?.content
                || '/pgtrek/api/beacon-performance';
            const summaryRoute = document.querySelector('meta[name="report-summary-route"]')?.content
                || '/pgtrek/api/report-summary';

            try {
                const headers = { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' };

                const [res, summaryRes] = await Promise.all([
                    fetch(`${beaconRoute}?${params.toString()}`, { headers }),
                    fetch(`${summaryRoute}?${params.toString()}`, { headers }),
                ]);

                if (!res.ok) {
                    throw new Error('Failed to load report (HTTP ' + res.status + ')');
                }
                if (!summaryRes.ok) {
                    throw new Error('Failed to load report summary (HTTP ' + summaryRes.status + ')');
                }

                const json = await res.json();
                this.pointCompletion = json.data.point_completion;
                this.timeImplement = json.data.time_implement;

                const summaryJson = await summaryRes.json();
                this.personnel = summaryJson.data.personnel;
                this.absentDiscipline = summaryJson.data.absent_discipline;
                this.alertPoint = summaryJson.data.alert_point;

                this.alertRows = [];
                (this.alertPoint.groups || []).forEach((g, gi) => {
                    const reasons = g.reasons.length ? g.reasons : [{ reason: '—', qty: 0 }];
                    reasons.forEach((r, ri) => {
                        this.alertRows.push({
                            groupIndex: gi + 1,
                            aspect: g.aspect,
                            groupQty: g.qty,
                            groupSpan: reasons.length,
                            isFirst: ri === 0,
                            reason: r.reason,
                            qty: r.qty,
                        });
                    });
                });
            } catch (e) {
                this.error = e.message || 'Failed to load report.';
            } finally {
                this.loading = false;
            }
        },
    };
}
