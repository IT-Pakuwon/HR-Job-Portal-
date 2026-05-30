// ============================================================
// tracking.js — Booking Car
// Approval tracking: timeline, status progression, messages
// ============================================================

const BookingCarTracking = {

    // --------------------------------------------------------
    // STATE
    // --------------------------------------------------------
    state: {
        currentEid:      null,
        trackingData:    null,
        isLoading:       false,
        autoRefreshTimer: null,
    },

    // --------------------------------------------------------
    // INIT
    // --------------------------------------------------------
    init() {
        // Can be called to auto-refresh tracking
    },

    // --------------------------------------------------------
    // FETCH TRACKING DATA
    // --------------------------------------------------------
    async fetch(eid) {
        if (!eid) {
            console.error('EID is required for tracking');
            return null;
        }

        BookingCarTracking.state.isLoading = true;
        BookingCarTracking.state.currentEid = eid;

        try {
            const response = await BookingCar.request(
                BookingCar.routes.tracking(eid)
            );

            if (response.success) {
                BookingCarTracking.state.trackingData = response;
                return response;
            } else {
                console.error('Failed to fetch tracking data:', response.message);
                return null;
            }

        } catch (err) {
            console.error('Tracking fetch error:', err);
            return null;

        } finally {
            BookingCarTracking.state.isLoading = false;
        }
    },

    // --------------------------------------------------------
    // RENDER TIMELINE IN DETAIL MODAL
    // --------------------------------------------------------
    async renderInDetail(eid) {
        const data = await BookingCarTracking.fetch(eid);
        if (!data) return;

        const timelineContainer = document.getElementById('bookingTrackingTimeline');
        if (!timelineContainer) return;

        const html = BookingCarTracking.renderTimeline(data.steps ?? []);
        timelineContainer.innerHTML = html;
    },

    // --------------------------------------------------------
    // RENDER TIMELINE STEPS
    // --------------------------------------------------------
    renderTimeline(steps) {
        if (!steps || steps.length === 0) {
            return `
                <div class="py-6 text-center text-sm text-slate-400">
                    No tracking steps found.
                </div>`;
        }

        return `
            <div class="space-y-4">
                ${steps.map((step, index) => BookingCarTracking.renderStep(step, index, steps.length)).join('')}
            </div>`;
    },

    // --------------------------------------------------------
    // RENDER SINGLE STEP
    // --------------------------------------------------------
    renderStep(step, index, total) {
        const isLast = index === total - 1;
        const icon = BookingCarTracking.getStepIcon(step.status);
        const label = BookingCarTracking.getStatusLabel(step.status);
        const title = step.title ?? step.status_label ?? '-';
        const by = step.by ?? null;
        const at = step.at ?? null;
        const comment = step.comment ?? null;

        return `
            <div class="relative flex gap-3">

                ${!isLast ? `
                    <div class="absolute left-4 top-8 h-full w-px bg-slate-200 dark:bg-white/10"></div>
                ` : ''}

                <div class="shrink-0">
                    ${icon}
                </div>

                <div class="min-w-0 flex-1 pb-2">

                    <div class="flex items-center justify-between gap-2">

                        <div class="text-sm font-semibold text-slate-800 dark:text-slate-100">
                            ${title}
                        </div>

                        ${label}

                    </div>

                    ${by ? `
                        <div class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                            <i class="fa-solid fa-user mr-1"></i>${by}
                        </div>
                    ` : ''}

                    ${at ? `
                        <div class="mt-0.5 text-xs text-slate-400 dark:text-slate-500">
                            <i class="fa-solid fa-calendar mr-1"></i>${at}
                        </div>
                    ` : ''}

                    ${comment ? `
                        <div class="mt-2 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-800 dark:border-amber-500/20 dark:bg-amber-500/10 dark:text-amber-200">
                            <i class="fa-solid fa-comment-dots mr-1"></i>
                            <span>${BookingCarTracking.escapeHtml(comment)}</span>
                        </div>
                    ` : ''}

                </div>

            </div>`;
    },

    // --------------------------------------------------------
    // GET STEP ICON BY STATUS
    // --------------------------------------------------------
    getStepIcon(status) {
        const icons = {
            'C': `<div class="flex h-8 w-8 items-center justify-center rounded-full bg-slate-100 dark:bg-white/10">
                    <i class="fa-solid fa-paper-plane text-xs text-slate-500 dark:text-slate-400"></i>
                </div>`,

            'A': `<div class="flex h-8 w-8 items-center justify-center rounded-full bg-emerald-100 dark:bg-emerald-500/20">
                    <i class="fa-solid fa-check text-xs text-emerald-600 dark:text-emerald-400"></i>
                </div>`,

            'P': `<div class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-500/20">
                    <i class="fa-solid fa-clock text-xs text-blue-500 dark:text-blue-400"></i>
                </div>`,

            'D': `<div class="flex h-8 w-8 items-center justify-center rounded-full bg-amber-100 dark:bg-amber-500/20">
                    <i class="fa-solid fa-rotate-left text-xs text-amber-600 dark:text-amber-400"></i>
                </div>`,

            'R': `<div class="flex h-8 w-8 items-center justify-center rounded-full bg-red-100 dark:bg-red-500/20">
                    <i class="fa-solid fa-xmark text-xs text-red-600 dark:text-red-400"></i>
                </div>`,

            'X': `<div class="flex h-8 w-8 items-center justify-center rounded-full bg-slate-100 dark:bg-white/10">
                    <i class="fa-solid fa-ban text-xs text-slate-500 dark:text-slate-400"></i>
                </div>`,
        };

        return icons[status] ?? icons['P'];
    },

    // --------------------------------------------------------
    // GET STATUS LABEL BADGE
    // --------------------------------------------------------
    getStatusLabel(status) {
        const labels = {
            'C': '<span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Submitted</span>',
            'A': '<span class="text-xs font-semibold text-emerald-600 dark:text-emerald-400">Approved</span>',
            'P': '<span class="text-xs font-semibold text-blue-500 dark:text-blue-400">Waiting</span>',
            'D': '<span class="text-xs font-semibold text-amber-600 dark:text-amber-400">Revise</span>',
            'R': '<span class="text-xs font-semibold text-red-600 dark:text-red-400">Rejected</span>',
            'X': '<span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Cancelled</span>',
        };

        return labels[status] ?? labels['P'];
    },

    // --------------------------------------------------------
    // RENDER COMMENTS / MESSAGES
    // --------------------------------------------------------
    renderComments(comments) {
        if (!comments || comments.length === 0) {
            return `
                <div class="py-4 text-center text-sm text-slate-400">
                    No messages found.
                </div>`;
        }

        return `
            <div class="space-y-3">
                ${comments.map((msg) => BookingCarTracking.renderComment(msg)).join('')}
            </div>`;
    },

    // --------------------------------------------------------
    // RENDER SINGLE COMMENT
    // --------------------------------------------------------
    renderComment(message) {
        const title = message.title ?? message.username ?? 'System';
        const text = message.description ?? message.message ?? '';
        const createdAt = message.created_at ?? '';

        return `
            <div class="rounded-lg border border-slate-200 bg-white p-3 dark:border-white/10 dark:bg-white/[0.03]">

                <div class="flex items-center justify-between gap-2">

                    <div class="font-medium text-sm text-slate-800 dark:text-slate-100">
                        <i class="fa-solid fa-user-circle mr-1 text-slate-400"></i>
                        ${BookingCarTracking.escapeHtml(title)}
                    </div>

                    <span class="text-xs text-slate-400">
                        ${createdAt}
                    </span>

                </div>

                <div class="mt-2 text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                    ${BookingCarTracking.escapeHtml(text)}
                </div>

            </div>`;
    },

    // --------------------------------------------------------
    // GET OVERALL STATUS TEXT
    // --------------------------------------------------------
    getStatusText(status) {
        const statusMap = {
            'P': 'Pending',
            'C': 'Completed',
            'R': 'Rejected',
            'D': 'Revise',
            'X': 'Cancelled',
            'F': 'Processed',
        };

        return statusMap[status] ?? status;
    },

    // --------------------------------------------------------
    // GET OVERALL STATUS BADGE
    // --------------------------------------------------------
    getStatusBadge(status) {
        const badges = {
            'P': 'bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-300',
            'C': 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300',
            'R': 'bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-300',
            'D': 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300',
            'X': 'bg-slate-100 text-slate-600 dark:bg-white/10 dark:text-slate-400',
            'F': 'bg-indigo-100 text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-300',
        };

        const cls = badges[status] ?? badges['P'];
        const text = BookingCarTracking.getStatusText(status);

        return `<span class="inline-flex items-center rounded-lg px-3 py-1 text-xs font-semibold ${cls}">
                    ${text}
                </span>`;
    },

    // --------------------------------------------------------
    // GET APPROVAL PROGRESS PERCENTAGE
    // --------------------------------------------------------
    getProgressPercentage(steps) {
        if (!steps || steps.length === 0) return 0;

        const approved = steps.filter(s => s.status === 'A' || s.status === 'C').length;
        return Math.round((approved / steps.length) * 100);
    },

    // --------------------------------------------------------
    // RENDER PROGRESS BAR
    // --------------------------------------------------------
    renderProgressBar(steps) {
        const progress = BookingCarTracking.getProgressPercentage(steps);

        return `
            <div class="space-y-2">

                <div class="flex items-center justify-between">

                    <span class="text-sm font-medium text-slate-700 dark:text-slate-200">
                        Approval Progress
                    </span>

                    <span class="text-sm font-semibold text-slate-600 dark:text-slate-300">
                        ${progress}%
                    </span>

                </div>

                <div class="h-2 w-full overflow-hidden rounded-full bg-slate-200 dark:bg-white/10">

                    <div class="h-full bg-gradient-to-r from-blue-500 to-emerald-500 transition-all duration-500"
                        style="width: ${progress}%">
                    </div>

                </div>

            </div>`;
    },

    // --------------------------------------------------------
    // FORMAT TRACKING DATE
    // --------------------------------------------------------
    formatTrackingDate(dateString) {
        if (!dateString) return '-';

        try {
            const date = new Date(dateString);
            const day = String(date.getDate()).padStart(2, '0');
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const year = date.getFullYear();
            const hours = String(date.getHours()).padStart(2, '0');
            const minutes = String(date.getMinutes()).padStart(2, '0');

            return `${day}/${month}/${year} ${hours}:${minutes}`;
        } catch (err) {
            return dateString;
        }
    },

    // --------------------------------------------------------
    // AUTO-REFRESH TRACKING (poll every 10 seconds)
    // --------------------------------------------------------
    startAutoRefresh(eid, intervalMs = 10000) {
        if (BookingCarTracking.state.autoRefreshTimer) {
            clearInterval(BookingCarTracking.state.autoRefreshTimer);
        }

        BookingCarTracking.state.autoRefreshTimer = setInterval(() => {
            BookingCarTracking.renderInDetail(eid);
        }, intervalMs);
    },

    // --------------------------------------------------------
    // STOP AUTO-REFRESH
    // --------------------------------------------------------
    stopAutoRefresh() {
        if (BookingCarTracking.state.autoRefreshTimer) {
            clearInterval(BookingCarTracking.state.autoRefreshTimer);
            BookingCarTracking.state.autoRefreshTimer = null;
        }
    },

    // --------------------------------------------------------
    // ESCAPE HTML (prevent XSS)
    // --------------------------------------------------------
    escapeHtml(text) {
        if (!text) return '';

        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    },

    // --------------------------------------------------------
    // CHECK IF DOCUMENT IS COMPLETED
    // --------------------------------------------------------
    isCompleted(status) {
        return ['C', 'R', 'X', 'F'].includes(status);
    },

    // --------------------------------------------------------
    // CHECK IF DOCUMENT IS PENDING APPROVAL
    // --------------------------------------------------------
    isPendingApproval(status) {
        return status === 'P';
    },

    // --------------------------------------------------------
    // CHECK IF DOCUMENT NEEDS REVISION
    // --------------------------------------------------------
    needsRevision(status) {
        return status === 'D';
    },

    // --------------------------------------------------------
    // GET NEXT EXPECTED ACTION
    // --------------------------------------------------------
    getNextAction(status) {
        const actions = {
            'P': 'Waiting for approver action',
            'C': 'Booking has been approved',
            'R': 'Booking was rejected',
            'D': 'Please revise and resubmit',
            'X': 'Booking was cancelled',
            'F': 'Ready for execution',
        };

        return actions[status] ?? 'No pending actions';
    },

    // --------------------------------------------------------
    // GET ESTIMATED TIME TO COMPLETION
    // --------------------------------------------------------
    getEstimatedCompletion(steps) {
        if (!steps || steps.length === 0) return null;

        const completed = steps.filter(s => s.status === 'A' || s.status === 'C');
        const remaining = steps.length - completed.length;

        if (remaining === 0) return null;

        // Rough estimate: 2 hours per approval level
        const hoursNeeded = remaining * 2;
        const date = new Date();
        date.setHours(date.getHours() + hoursNeeded);

        return BookingCarTracking.formatTrackingDate(date.toISOString());
    },
};
