window.BookingCar = window.BookingCar || {};

// =====================================================
// CREATE ROUTE
// =====================================================

window.createRouteRow = function (index, from = "", destination = "") {
    return `
        <tr>

            <td class="px-4 py-3 text-sm font-medium text-gray-500">
                ${index}
            </td>

            <td class="px-4 py-3">
                <input
                    type="text"
                    name="location_from[]"
                    value="${BookingCar.escapeHtml(from)}"
                    placeholder="Pickup location"
                    class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm focus:border-black focus:outline-none"
                    required
                >
            </td>

            <td class="px-4 py-3">
                <input
                    type="text"
                    name="destination[]"
                    value="${BookingCar.escapeHtml(destination)}"
                    placeholder="Destination"
                    class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm focus:border-black focus:outline-none"
                    required
                >
            </td>

            <td class="px-4 py-3 text-right">
                <button
                    type="button"
                    onclick="window.removeRouteRow(this)"
                    class="rounded-lg bg-red-50 px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-100"
                >
                    Remove
                </button>
            </td>

        </tr>
    `;
};

window.refreshRouteNumber = function () {
    document
        .querySelectorAll("#createRouteTableBody tr")
        .forEach((tr, index) => {
            const firstCell = tr.querySelector("td");

            if (firstCell) {
                firstCell.innerText = index + 1;
            }
        });
};

window.removeRouteRow = function (btn) {
    const tbody = document.getElementById("createRouteTableBody");

    if (!tbody) {
        return;
    }

    if (tbody.querySelectorAll("tr").length <= 1) {
        BookingCar.warning("At least one route is required");

        return;
    }

    btn.closest("tr")?.remove();

    window.refreshRouteNumber();
};

// =====================================================
// EDIT ROUTE
// =====================================================

window.createEditRouteRow = function (index, from = "", destination = "") {
    return `
        <tr>

            <td class="px-4 py-3 text-sm font-medium text-gray-500">
                ${index}
            </td>

            <td class="px-4 py-3">
                <input
                    type="text"
                    name="location_from[]"
                    value="${BookingCar.escapeHtml(from)}"
                    placeholder="Pickup location"
                    class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm focus:border-black focus:outline-none"
                    required
                >
            </td>

            <td class="px-4 py-3">
                <input
                    type="text"
                    name="destination[]"
                    value="${BookingCar.escapeHtml(destination)}"
                    placeholder="Destination"
                    class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm focus:border-black focus:outline-none"
                    required
                >
            </td>

            <td class="px-4 py-3 text-right">
                <button
                    type="button"
                    onclick="window.removeEditRouteRow(this)"
                    class="rounded-lg bg-red-50 px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-100"
                >
                    Remove
                </button>
            </td>

        </tr>
    `;
};

window.refreshEditRouteNumber = function () {
    document.querySelectorAll("#editRouteTableBody tr").forEach((tr, index) => {
        const firstCell = tr.querySelector("td");

        if (firstCell) {
            firstCell.innerText = index + 1;
        }
    });
};

window.removeEditRouteRow = function (btn) {
    const tbody = document.getElementById("editRouteTableBody");

    if (!tbody) {
        return;
    }

    if (tbody.querySelectorAll("tr").length <= 1) {
        BookingCar.warning("At least one route is required");

        return;
    }

    btn.closest("tr")?.remove();

    window.refreshEditRouteNumber();
};

// =====================================================
// EVENTS
// =====================================================

document.addEventListener("DOMContentLoaded", function () {
    document
        .getElementById("createAddRouteBtn")
        ?.addEventListener("click", function () {
            const tbody = document.getElementById("createRouteTableBody");

            if (!tbody) {
                return;
            }

            const index = tbody.querySelectorAll("tr").length + 1;

            tbody.insertAdjacentHTML("beforeend", window.createRouteRow(index));
        });

    document
        .getElementById("editAddRouteBtnEdit")
        ?.addEventListener("click", function () {
            const tbody = document.getElementById("editRouteTableBody");

            if (!tbody) {
                return;
            }

            const index = tbody.querySelectorAll("tr").length + 1;

            tbody.insertAdjacentHTML(
                "beforeend",
                window.createEditRouteRow(index),
            );
        });
});
