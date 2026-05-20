window.BookingCar = window.BookingCar || {};

// =====================================================
// STRING
// =====================================================

BookingCar.escapeHtml = function (str = "") {

    return String(str)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
};

// =====================================================
// STATUS
// =====================================================

BookingCar.statusBadge = function (status) {

    const map = {
        P: "bg-blue-100 text-blue-700",
        C: "bg-emerald-100 text-emerald-700",
        D: "bg-yellow-100 text-yellow-700",
        R: "bg-red-100 text-red-700",
        X: "bg-gray-200 text-gray-700"
    };

    const label = {
        P: "Pending",
        C: "Completed",
        D: "Revise",
        R: "Rejected",
        X: "Cancelled"
    };

    return `
        <span class="rounded-full px-2 py-1 text-[10px] font-semibold ${map[status] || "bg-gray-100 text-gray-700"}">
            ${label[status] || "-"}
        </span>
    `;
};

BookingCar.statusHtml = function (status) {

    const map = {
        P: "bg-blue-100 text-blue-700",
        C: "bg-emerald-100 text-emerald-700",
        D: "bg-yellow-100 text-yellow-700",
        R: "bg-red-100 text-red-700",
        X: "bg-gray-200 text-gray-700"
    };

    const label = {
        P: "Pending",
        C: "Completed",
        D: "Revise",
        R: "Rejected",
        X: "Cancelled"
    };

    return `
        <div class="rounded-full px-3 py-1 text-xs font-medium ${map[status] || "bg-gray-100 text-gray-700"}">
            ${label[status] || "-"}
        </div>
    `;
};

// =====================================================
// ALERT
// =====================================================

BookingCar.success = function (message) {

    return Swal.fire({
        icon: "success",
        title: "Success",
        text: message,
        timer: 1800,
        showConfirmButton: false
    });
};

BookingCar.error = function (message) {

    return Swal.fire({
        icon: "error",
        title: "Error",
        text: message
    });
};

BookingCar.warning = function (message) {

    return Swal.fire({
        icon: "warning",
        title: "Warning",
        text: message
    });
};

BookingCar.confirm = function (
    title,
    text = "",
    confirmText = "Confirm",
    icon = "question"
) {

    return Swal.fire({
        icon,
        title,
        text,
        showCancelButton: true,
        confirmButtonText: confirmText,
        cancelButtonText: "Cancel"
    });
};

// =====================================================
// TOKEN
// =====================================================

BookingCar.csrfToken = function () {

    return document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute("content");
};

// =====================================================
// DATE TIME
// =====================================================

BookingCar.timeOnly = function (datetime) {

    if (!datetime) {
        return "-";
    }

    return String(datetime)
        .substring(11, 16);
};

BookingCar.dateOnly = function (datetime) {

    if (!datetime) {
        return "-";
    }

    return String(datetime)
        .substring(0, 10);
};

// =====================================================
// BUTTON LOADING
// =====================================================

BookingCar.setLoading = function (
    button,
    text = "Loading..."
) {

    if (!button) {
        return;
    }

    button.disabled = true;

    button.dataset.originalText =
        button.innerHTML;

    button.innerHTML = text;
};

BookingCar.clearLoading = function (
    button
) {

    if (!button) {
        return;
    }

    button.disabled = false;

    button.innerHTML =
        button.dataset.originalText ||
        button.innerHTML;
};

// =====================================================
// FETCH
// =====================================================

BookingCar.fetchJson = async function (
    url,
    options = {}
) {

    const response = await fetch(url, {
        headers: {
            "X-Requested-With": "XMLHttpRequest",
            "Accept": "application/json",
            ...(options.headers || {})
        },
        ...options
    });

    const contentType =
        response.headers.get(
            "content-type"
        ) || "";

    let result = {};

    if (
        contentType.includes(
            "application/json"
        )
    ) {

        result =
            await response.json();

    } else {

        const text =
            await response.text();

        throw new Error(
            text ||
            `HTTP ${response.status}`
        );
    }

    if (!response.ok) {

        throw new Error(
            result.message ||
            `HTTP ${response.status}`
        );
    }

    return result;
};

// =====================================================
// POST FORM
// =====================================================

BookingCar.postForm = async function (
    url,
    formData
) {

    return BookingCar.fetchJson(url, {
        method: "POST",
        headers: {
            "X-CSRF-TOKEN":
                BookingCar.csrfToken()
        },
        body: formData
    });
};

// =====================================================
// POST JSON
// =====================================================

BookingCar.postJson = async function (
    url,
    data = {}
) {

    return BookingCar.fetchJson(url, {
        method: "POST",
        headers: {
            "Content-Type":
                "application/json",
            "X-CSRF-TOKEN":
                BookingCar.csrfToken()
        },
        body: JSON.stringify(data)
    });
};
