$(document).on("keyup", ".inventory-search", async function () {
    const input = $(this);

    const keyword = input.val();

    const container = input.closest("td").find(".inventory-result");

    if (keyword.length < 2) {
        container.addClass("hidden").html("");

        return;
    }

    try {
        const res = await $.ajax({
            url: `/it-recommendation/inventory-search`,
            type: "GET",
            data: {
                q: keyword,
            },
        });

        let html = "";

        if (res.length === 0) {
            html = `
                            <div class="px-3 py-2 text-xs text-gray-400">
                                No inventory found
                            </div>
                        `;
        } else {
            res.forEach((row) => {
                html += `
                            <button
                                type="button"
                                class="inventory-select group flex w-full flex-col gap-1 border-b border-gray-100 px-4 py-3 text-left transition hover:bg-indigo-50 dark:border-white/5 dark:hover:bg-white/[0.03]"
                                data-id="${row.inventoryid}"
                                data-name="${row.inventory_descr}"
                                data-uom="${row.purchase_unit ?? ""}">

                            <span class="line-clamp-2 text-sm font-medium leading-snug text-gray-700 dark:text-gray-200">
                                    ${row.inventory_descr}
                                </span>

                                <div class="flex items-center gap-2 text-[11px] text-gray-400">

                                    <span class="rounded bg-gray-100 px-2 py-0.5 dark:bg-white/10">
                                        ${row.inventoryid}
                                    </span>

                                </div>
                            </button>
                        `;
            });
        }

        container.removeClass("hidden").html(html);
    } catch (err) {
        console.log(err);
    }
});

$(document).on("click", ".inventory-select", function () {
    const btn = $(this);

    const row = btn.closest("tr");

    row.find(".inventory-search").val(btn.data("name"));

    row.find(".inventory-id").val(btn.data("id"));

    row.find(".item-uom").val(btn.data("uom") || "");

    row.find(".inventory-result").addClass("hidden").html("");
});

$(document).on("keyup", ".edit-inventory-search", async function () {
    const input = $(this);

    const keyword = input.val();

    const container = input.closest("td").find(".edit-inventory-result");

    if (keyword.length < 2) {
        container.addClass("hidden").html("");

        return;
    }

    try {
        const res = await $.ajax({
            url: `/it-recommendation/inventory-search`,
            type: "GET",

            data: {
                q: keyword,
            },
        });

        let html = "";

        if (res.length === 0) {
            html = `
                            <div class="px-3 py-2 text-xs text-gray-400">
                                No inventory found
                            </div>
                        `;
        } else {
            res.forEach((row) => {
                html += `
                                <button
                                    type="button"
                                    class="edit-inventory-select group flex w-full flex-col gap-1 border-b border-gray-100 px-4 py-3 text-left transition hover:bg-indigo-50 dark:border-white/5 dark:hover:bg-white/[0.03]"
                                    data-id="${row.inventoryid}"
                                    data-name="${row.inventory_descr}"
                                    data-uom="${row.purchase_unit ?? ""}">

                                    <span class="line-clamp-2 text-sm font-medium leading-snug text-gray-700 dark:text-gray-200">
                                        ${row.inventory_descr}
                                    </span>

                                    <div class="flex items-center gap-2 text-[11px] text-gray-400">

                                        <span class="rounded bg-gray-100 px-2 py-0.5 dark:bg-white/10">
                                            ${row.inventoryid}
                                        </span>

                                    </div>

                                </button>
                            `;
            });
        }

        container.removeClass("hidden").html(html);
    } catch (err) {
        console.log(err);
    }
});

$(document).on("click", ".edit-inventory-select", function () {
    const btn = $(this);

    const row = btn.closest("tr");

    row.find(".edit-inventory-search").val(btn.data("name"));

    row.find(".edit-inventory-id").val(btn.data("id"));

    row.find(".edit-item-uom").val(btn.data("uom") || "");

    row.find(".edit-inventory-result").addClass("hidden").html("");
});

$(document).on("click", function (e) {
    if (
        !$(e.target).closest(".edit-inventory-search, .edit-inventory-result")
            .length
    ) {
        $(".edit-inventory-result").addClass("hidden");
    }
});

$(document).on("click", function (e) {
    if (!$(e.target).closest(".inventory-search, .inventory-result").length) {
        $(".inventory-result").addClass("hidden");
    }
});
