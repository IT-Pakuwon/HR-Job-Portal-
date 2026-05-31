// ============================================================
// helper.js — Voucher Taxi
// Validator utilities (no moment dependency)
// ============================================================

const VoucherTaxiHelper = {

    // --------------------------------------------------------
    // DATE VALIDATORS
    // --------------------------------------------------------
    isValidDate(dateString) {
        if (!dateString) return false;
        return !isNaN(new Date(dateString).getTime());
    },

    isDateInPast(date) {
        if (!date) return false;
        const d = new Date(date); d.setHours(0,0,0,0);
        const t = new Date();     t.setHours(0,0,0,0);
        return d < t;
    },

    isDateToday(date) {
        if (!date) return false;
        return new Date(date).toDateString() === new Date().toDateString();
    },

    isDateInFuture(date) {
        if (!date) return false;
        const d = new Date(date); d.setHours(0,0,0,0);
        const t = new Date();     t.setHours(0,0,0,0);
        return d > t;
    },

    getDaysDifference(date1, date2) {
        return Math.round((new Date(date2) - new Date(date1)) / 86_400_000);
    },

    compareDates(date1, date2) {
        return new Date(date1).toDateString() === new Date(date2).toDateString();
    },

    getTimeAgo(date) {
        if (!date) return '';
        const diff  = Date.now() - new Date(date).getTime();
        const secs  = Math.round(diff / 1000);
        const mins  = Math.round(secs / 60);
        const hours = Math.round(mins / 60);
        const days  = Math.round(hours / 24);
        if (secs  < 60)  return 'just now';
        if (mins  < 60)  return `${mins} min${mins  !== 1 ? 's' : ''} ago`;
        if (hours < 24)  return `${hours} hr${hours !== 1 ? 's' : ''} ago`;
        if (days  < 30)  return `${days} day${days  !== 1 ? 's' : ''} ago`;
        return new Date(date).toLocaleDateString('id-ID');
    },

    // --------------------------------------------------------
    // FIELD VALIDATORS
    // --------------------------------------------------------
    isRequired(value)         { return !!value && String(value).trim() !== ''; },
    isNumber(value)           { return !isNaN(parseFloat(value)) && isFinite(value); },
    isPositive(value)         { return this.isNumber(value) && parseFloat(value) > 0; },
    minLength(value, min)     { return String(value).trim().length >= min; },
    maxLength(value, max)     { return String(value).trim().length <= max; },
    isEmpty(str)              { return !str || String(str).trim() === ''; },

    // --------------------------------------------------------
    // STRING UTILITIES
    // --------------------------------------------------------
    truncate(text, len = 50)  { return VoucherTaxi.truncate(text, len); },

    capitalize(str) {
        if (!str) return '';
        return str.charAt(0).toUpperCase() + str.slice(1).toLowerCase();
    },

    titleCase(str) {
        if (!str) return '';
        return str.replace(/\w\S*/g, w => w.charAt(0).toUpperCase() + w.slice(1).toLowerCase());
    },

    getInitials(name) {
        if (!name) return '';
        return name.split(' ').map(w => w[0]).join('').substring(0, 2).toUpperCase();
    },

    getColorFromString(str) {
        const colors = ['#3B82F6','#10B981','#F59E0B','#EF4444','#8B5CF6','#EC4899','#06B6D4','#F97316','#6366F1'];
        let hash = 0;
        for (let i = 0; i < str.length; i++) hash = str.charCodeAt(i) + ((hash << 5) - hash);
        return colors[Math.abs(hash) % colors.length];
    },

    // --------------------------------------------------------
    // OBJECT / JSON UTILITIES
    // --------------------------------------------------------
    deepClone(obj)        { return JSON.parse(JSON.stringify(obj)); },

    safeJsonParse(str, fallback = null) {
        try { return JSON.parse(str); } catch { return fallback; }
    },

    // --------------------------------------------------------
    // STATUS HELPERS  (delegates to core)
    // --------------------------------------------------------
    getStatusBadge(status)  { return VoucherTaxi.statusBadge(status); },
    getStatusColor(status)  { return VoucherTaxi.statusColor(status); },
    getStatusIcon(status)   { return VoucherTaxi.statusIcon(status); },

    formatStatusLabel(status) {
        return VoucherTaxi.statusMap[status]?.label ?? status ?? 'Unknown';
    },

    // --------------------------------------------------------
    // NUMBER / CURRENCY
    // --------------------------------------------------------
    formatNumber(num)   { return new Intl.NumberFormat('id-ID').format(num); },
    parseNumber(str)    { return parseInt(String(str).replace(/\D/g, '')) || 0; },

    // --------------------------------------------------------
    // SEARCH HIGHLIGHT  (delegates to core)
    // --------------------------------------------------------
    highlightText(text, term) { return VoucherTaxi.highlightText(text, term); },
};
