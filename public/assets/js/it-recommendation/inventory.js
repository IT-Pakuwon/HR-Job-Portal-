async function searchInventory({ keyword, container, selectClass }) {
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

        renderInventoryResult({
            container,

            rows: res,

            selectClass,
        });
    } catch (err) {
        // console.log(err);
    }
}

function renderInventoryResult({ container, rows = [], selectClass }) {
    let html = "";

    if (rows.length === 0) {
        html = `
            <div class="
                px-3 py-2

                text-xs

                text-gray-400
            ">
                No inventory found
            </div>
        `;
    } else {
        rows.forEach((row) => {
            html += `

                <button
                    type="button"

                    class="
                        ${selectClass}

                        group

                        flex
                        w-full
                        flex-col
                        gap-1

                        border-b border-gray-100
                        dark:border-white/5

                        px-4 py-3

                        text-left

                        transition-all
                        duration-150

                        hover:bg-indigo-50
                        dark:hover:bg-white/[0.03]
                    "

                    data-id="${row.inventoryid}"

                    data-name="${row.inventory_descr}"

                    data-uom="${row.purchase_unit ?? ""}"
                >

                    <span class="
                        line-clamp-2

                        text-sm
                        font-medium
                        leading-snug

                        text-gray-700
                        dark:text-gray-200
                    ">
                        ${row.inventory_descr}
                    </span>

                    <div class="
                        flex
                        items-center
                        gap-2

                        text-[11px]

                        text-gray-400
                    ">

                        <span class="
                            rounded

                            bg-gray-100
                            dark:bg-white/10

                            px-2 py-0.5
                        ">
                            ${row.inventoryid}
                        </span>

                    </div>

                </button>

            `;
        });
    }

    container.removeClass("hidden").html(html);
}

function selectInventory({
    btn,
    searchClass,
    idClass,
    uomClass,
    resultClass
}) {

    const row = btn.closest("tr");

    row.find(searchClass).val(btn.data("name"));

    row.find(idClass).val(btn.data("id"));

    row.find(uomClass).val(btn.data("uom") || "");

    const result = btn.closest(resultClass);

    result.hide();
    result.empty();
}
$(document).on("focus", ".inventory-search", function () {

    const result = $(this)
        .closest("td")
        .find(".inventory-result");

    if (!result.children().length) {
        result.addClass("hidden");
    }
});

$(document).on("keyup", ".inventory-search", async function () {
    const input = $(this);

    searchInventory({
        keyword: input.val(),

        container: input.closest("td").find(".inventory-result"),

        selectClass: "inventory-select",
    });
});

$(document).on("keyup", ".edit-inventory-search", async function () {
    const input = $(this);

    searchInventory({
        keyword: input.val(),

        container: input.closest("td").find(".edit-inventory-result"),

        selectClass: "edit-inventory-select",
    });
});

$(document).on("click", ".inventory-select", function (e) {

    e.preventDefault();

    selectInventory({
        btn: $(this),

        searchClass: ".inventory-search",

        idClass: ".inventory-id",

        uomClass: ".item-uom",

        resultClass: ".inventory-result",
    });
});

$(document).on("click", ".edit-inventory-select", function () {
    selectInventory({
        btn: $(this),

        searchClass: ".edit-inventory-search",

        idClass: ".edit-inventory-id",

        uomClass: ".edit-item-uom",

        resultClass: ".edit-inventory-result",
    });
});

$(document).on("click", function (e) {
    if (
        !$(e.target).closest(`
                    .inventory-search,
                    .inventory-result,
                    .edit-inventory-search,
                    .edit-inventory-result
                `).length
    ) {
        $(".inventory-result").addClass("hidden");

        $(".edit-inventory-result").addClass("hidden");
    }
});
