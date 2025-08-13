<x-app-layout>
<div x-data="canvassSheet()" class="p-4">

    {{-- ======= Header & tombol Add Vendor ======= --}}
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-lg font-semibold">Canvass Sheet : {{ $docno ?? 'CSxxxx' }}</h1>

        {{-- klik tombol akan membuka dropdown Select2 --}}
        <button @click="$nextTick(()=>$('#vendorSelect').select2('open'))"
            class="flex items-center gap-1 bg-indigo-600 text-white px-3 py-1 rounded hover:bg-indigo-700">
            ➕ Add Vendor
        </button>
    </div>

    {{-- ======= Select2 (hidden)  ======= --}}
    <select id="vendorSelect" class="hidden"></select>

    {{-- ======= Tabel Canvass ======= --}}
    <div class="overflow-x-auto">
        <table class="table-auto w-max whitespace-nowrap border">
            <thead>
                <tr class="bg-gray-100 align-top">
                    <th class="border px-3 py-2 w-64">Description</th>
                    <th class="border px-3 py-2 w-16 text-center">Qty</th>
                    <th class="border px-3 py-2 w-16 text-center">UOM</th>

                    <template x-for="(v, vi) in vendors" :key="vi">
                        <th class="border px-3 py-2 w-60 relative">
                            <div class="font-semibold" x-text="v.name"></div>
                            <div class="text-xs text-gray-500 leading-4">
                                <div x-text="'✉️ ' + v.contact"></div>
                                <div x-text="'☎️ ' + v.phone"></div>
                                <div x-text="'🏠 ' + v.address"></div>
                            </div>
                            <button @click="removeVendor(vi)"
                                class="absolute -top-1 -right-1 bg-red-600 text-white rounded-full h-5 w-5 flex items-center justify-center text-xs hover:bg-red-700">
                                🗑
                            </button>
                        </th>
                    </template>
                </tr>
            </thead>

            <tbody>
                <template x-for="(item, ii) in items" :key="ii">
                    <tr>
                        <td class="border px-3 py-2" x-text="item.desc"></td>
                        <td class="border px-3 py-2 text-center" x-text="item.qty"></td>
                        <td class="border px-3 py-2 text-center" x-text="item.uom"></td>

                        <template x-for="(v, vi) in vendors" :key="vi">
                            <td class="border px-3 py-2">
                                <input type="number" min="0" step="0.01"
                                       class="w-full border rounded px-1 text-right"
                                       x-model="prices[ii][vi]">
                            </td>
                        </template>
                    </tr>
                </template>
            </tbody>
        </table>

        <div x-show="vendors.length === 0"
             class="text-sm italic text-gray-500 mt-2">
            Belum ada vendor – klik “Add Vendor”.
        </div>
    </div>
</div>

{{-- ======= CDN Alpine & Select2 ======= --}}
<script src="//unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<link  href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
function canvassSheet() {
    return {
        /* ---------- State ---------- */
        vendors: [],          // kolom dinamis
        vendorMaster: [],     // diisi lewat fetch
        items: [
            {desc:'Adaptor Output 12V 2A', qty:15, uom:'PCS'},
            {desc:'Lampu Bohlam Viva 12V', qty:10, uom:'PCS'},
            {desc:'Kawat Las Stainless',   qty: 3, uom:'KG'},
        ],
        prices: [],

        /* ---------- Lifecycle ---------- */
        init() {
            // siapkan array harga kosong per item
            this.items.forEach((_, i) => this.prices[i] = []);

            /* --- ambil master vendor dari API --- */
            fetch('/vendors')          // ⇐ route/api.php
                .then(r => r.json())
                .then(data => {
                    this.vendorMaster = data;
                    // populate <select> options
                    const $sel = $('#vendorSelect');
                    data.forEach(v => $sel.append(
                        $('<option>', {value:v.id, text:v.name})
                    ));
                });

            /* --- init Select2 --- */
            const sel = $('#vendorSelect').select2({
                dropdownParent:$('body'),
                width:'250px',
                placeholder:'Pilih vendor'
            });

            sel.on('select2:select', e => {
                const id = +e.params.data.id;
                const vendor = this.vendorMaster.find(v => v.id === id);
                if (!vendor) return;

                // cek duplikat
                if (this.vendors.some(v => v.id === id)) {
                    alert('Vendor sudah ada');
                    sel.val(null).trigger('change'); return;
                }

                // tambah vendor & kolom harga
                this.vendors.push({...vendor});
                this.items.forEach((_, i) => this.prices[i].push(0));
                sel.val(null).trigger('change');
            });
        },

        /* ---------- Methods ---------- */
        removeVendor(idx) {
            this.vendors.splice(idx,1);
            this.items.forEach((_, i) => this.prices[i].splice(idx,1));
        }
    };
}
</script>
</x-app-layout>
