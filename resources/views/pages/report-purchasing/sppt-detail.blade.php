<div class="space-y-4">
  <!-- FILTER PANEL -->
  <div class="p-6 bg-gray-50/60 rounded-2xl border border-gray-200 shadow-sm">
    <div class="grid grid-cols-1 gap-4 md:grid-cols-7">
      <div>
        <label class="text-xs text-gray-500">Date From</label>
        <input
          type="date"
          id="sppt_date_from"
          class="w-full px-3 py-2 text-sm bg-white rounded-lg border border-gray-200 transition focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
        />
      </div>

      <div>
        <label class="text-xs text-gray-500">Date To</label>
        <input
          type="date"
          id="sppt_date_to"
          class="w-full px-3 py-2 text-sm bg-white rounded-lg border border-gray-200 transition focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
        />
      </div>

      <div>
        <label class="text-xs text-gray-500">SPPT No</label>
        <input
          type="text"
          id="spptid"
          placeholder="PT-xxxx"
          class="w-full px-3 py-2 text-sm bg-white rounded-lg border border-gray-200 transition focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
        />
      </div>

      <div>
        <label class="text-xs text-gray-500">Inventory ID</label>
        <input
          type="text"
          id="inventoryid_sppt"
          placeholder="Item code"
          class="w-full px-3 py-2 text-sm bg-white rounded-lg border border-gray-200 transition focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
        />
      </div>

      <div>
        <label class="text-xs text-gray-500">Status</label>
        <select
          id="status"
          class="w-full px-3 py-2 text-sm bg-white rounded-lg border border-gray-200"
        >
          <option value="">All</option>
          <option value="P">On Progress</option>
          <option value="C">Completed</option>
          <option value="R">Rejected</option>
          <option value="D">Revise</option>
          <option value="X">Cancel</option>
        </select>
      </div>

      <div class="flex items-end gap-2 md:col-span-2">
        <button
          id="filterSppt"
          class="px-4 py-2 text-sm text-white bg-gray-900 rounded-lg"
        >
          Apply
        </button>

        <button
          id="resetSppt"
          class="px-4 py-2 text-sm bg-white rounded-lg border border-gray-200"
        >
          Reset
        </button>

        <button
          id="exportSppt"
          class="px-4 py-2 text-sm text-emerald-700 bg-emerald-50 rounded-lg border border-emerald-200"
        >
          Export
        </button>
      </div>
    </div>
  </div>

  <!-- TABLE -->
  <div class="bg-white rounded-2xl border border-gray-200 shadow-sm">
    <div class="px-6 py-4 border-b border-gray-200">
      <h2 class="text-sm font-semibold text-gray-800">SPPT Detail</h2>
    </div>

    <div class="overflow-x-auto p-5">
      <table id="spptTable" class="min-w-full text-sm">
        <thead class="text-xs text-gray-500 bg-gray-50 uppercase">
          <tr>
            <th>Date</th>
            <th>SPPT No</th>
            <th>Tenant</th>
            <th>Unit</th>
            <th>PIC</th>
            <th>Department</th>
            <th>Requester</th>
            <th>Purchasing</th>
            <th>Inventory ID</th>
            <th>Description</th>
            <th>Qty</th>
            <th>UOM</th>
            <th>Warehouse</th>
            <th>Status</th>
          </tr>
        </thead>
      </table>
    </div>
  </div>
</div>

<script>
  $(function () {
    var table = $('#spptTable').DataTable({
      processing: true,
      serverSide: true,
      responsive: true,

      searching: false,
      dom:
        "<'flex items-center justify-between mb-3'<'text-sm'l>>" +
        'rt' +
        "<'flex items-center justify-between mt-3'<'text-sm'i><'text-sm'p>>",

      lengthMenu: [
        [10, 25, 50, 100, -1],
        [10, 25, 50, 100, 'All'],
      ],

      pageLength: 10,

      ajax: {
        url: '{{ route("report.purchasing.json") }}',
        data: function (d) {
          d.report = 'sppt';

          d.date_from = $('#sppt_date_from').val();
          d.date_to = $('#sppt_date_to').val();

          d.spptid = $('#spptid').val();
          d.inventoryid = $('#inventoryid_sppt').val();
          d.status = $('#status').val();
        },
      },

      columns: [
        {
          data: 'spptdate',
        },
        {
          data: 'spptid',
        },
        {
          data: 'nama_tenant',
        },
        {
          data: 'no_unit_tenant',
        },
        {
          data: 'pic_pengawas',
        },
        {
          data: 'department_name',
        },
        {
          data: 'requester',
        },
        {
          data: 'purchasing',
        },
        {
          data: 'inventoryid',
        },
        {
          data: 'inventory_descr',
        },
        {
          data: 'qty',
        },
        {
          data: 'uom',
        },
        {
          data: 'siteid',
        },
        {
          data: 'status',
        },
      ],

      order: [[0, 'desc']],
    });

    $('#filterSppt').click(function () {
      table.ajax.reload();
    });

    $('#resetSppt').click(function () {
      $('#sppt_date_from').val('');
      $('#sppt_date_to').val('');
      $('#spptid').val('');
      $('#inventoryid_sppt').val('');
      $('#status').val('');

      table.ajax.reload();
    });

    $('#exportSppt').click(function () {
      let url = '{{ route("report.purchasing.export") }}?report=sppt';

      url += '&date_from=' + $('#sppt_date_from').val();
      url += '&date_to=' + $('#sppt_date_to').val();
      url += '&spptid=' + $('#spptid').val();
      url += '&inventoryid=' + $('#inventoryid_sppt').val();

      window.location.href = url;
    });
  });
</script>
