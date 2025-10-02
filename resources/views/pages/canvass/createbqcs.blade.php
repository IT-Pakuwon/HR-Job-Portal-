<x-app-layout>
  <div class="max-w-7xl mx-auto p-6">
    <h2 class="text-xl font-bold mb-4">Create BQ from CS: {{ $cs->csid }}</h2>

    {{-- Header info singkat --}}
    <div class="grid grid-cols-2 gap-4 mb-4">
      <div class="p-4 bg-white rounded shadow">
        <div><b>Company:</b> {{ $cs->cpny_id }}</div>
        <div><b>Department:</b> {{ $cs->department_id }}</div>
        <div><b>BQ ID:</b> {{ $cs->bqid }}</div>
      </div>
      <div class="p-4 bg-white rounded shadow">
        <div><b>SPPJ/K/T:</b> {{ $cs->sppbjktid }}</div>
        <div><b>Requester:</b> {{ $cs->user_peminta }}</div>
      </div>
    </div>


    {{-- Detail Table --}}
    <form id="bqForm" class="bg-white rounded shadow p-4">
      @csrf
      <input type="hidden" name="csid" value="{{ $cs->csid }}">
      <input type="hidden" name="bqid" value="{{ $cs->bqid }}">
      <input type="hidden" name="cpny_id" value="{{ $cs->cpny_id }}">

      <div class="overflow-x-auto">
      <table class="min-w-full border">
        <thead>
          <tr class="bg-gray-50">
            <th class="border px-2 py-1">BQ No</th>
            <th class="border px-2 py-1">Line</th>
            <th class="border px-2 py-1">Description</th>
            <th class="border px-2 py-1">Qty</th>
            <th class="border px-2 py-1">UoM</th>
            @foreach($vendors as $v)
              <th class="border px-2 py-1 text-center">
                {{ $v['name'] }}<br>
                <span class="text-xs text-gray-500">✉️ {{ $v['cp'] ?? '-' }}</span><br>
                <span class="text-xs text-gray-500">☎️ {{ $v['telp'] ?? '-' }}</span><br>
                <span class="text-xs text-gray-500">🏠 {{ $v['addr'] ?? '-' }}</span><br>
                <span class="text-xs text-gray-500">Material / Jasa</span>
              </th>
            @endforeach
          </tr>
        </thead>
        <tbody>
          @foreach($bqDetails as $i => $d)
            <tr>
              <td class="border px-2 py-1">{{ $d->bq_no }}</td>
              <td class="border px-2 py-1">{{ $d->bq_line_no }}</td>
              <td class="border px-2 py-1">{{ $d->bq_descr }}</td>
              <td class="border px-2 py-1">
                <input type="number" step="0.01" min="0" class="bq-qty w-24 text-right border rounded px-2"
                       value="{{ number_format((float)$d->qty,2,'.','') }}">
              </td>
              <td class="border px-2 py-1">{{ $d->uom }}</td>

              @foreach($vendors as $v)
                <td class="border px-2 py-1">
                  <div class="flex gap-1">
                    <input type="text" value="{{ $d->est_material_price }}" class="w-24 text-right border rounded px-2" readonly>
                    <input type="text" value="{{ $d->est_jasa_price }}" class="w-24 text-right border rounded px-2" readonly>
                    <input type="number" step="0.01" min="0" value="0.00" class="bq-price-mat w-24 text-right border rounded px-2" placeholder="Material">
                    <input type="number" step="0.01" min="0" value="0.00" class="bq-price-jsa w-24 text-right border rounded px-2" placeholder="Jasa">
                  </div>
                </td>
              @endforeach
            </tr>
          @endforeach
        </tbody>
        <tfoot>
          <tr class="bg-gray-50 font-semibold">
            <td colspan="5" class="border px-2 py-2 text-right">Grand Total per Vendor</td>

            @foreach($vendors as $i => $v)
              <td class="border px-2 py-2 text-right">
                <div class="text-xs text-gray-500">
                  Material: <span class="sum-mat" data-vendor="{{ $i+1 }}">0</span>
                </div>
                <div class="text-xs text-gray-500">
                  Jasa: <span class="sum-jsa" data-vendor="{{ $i+1 }}">0</span>
                </div>
                <div class="mt-1">
                  Grand: <span class="sum-grand" data-vendor="{{ $i+1 }}">0</span>
                </div>
              </td>
            @endforeach
          </tr>
        </tfoot>

      </table>
      </div>

      <div class="mt-4 flex justify-end gap-2">
        <a href="{{ url()->previous() }}" class="px-4 py-2 rounded bg-gray-200">Cancel</a>
        <button type="button" id="btnSaveBQ" class="px-4 py-2 rounded bg-indigo-600 text-white">Save BQ</button>
      </div>
    </form>
  </div>

  <script>
      (function(){
        const vendors = @json($vendors);
        const $form   = document.getElementById('bqForm');
        const $btn    = document.getElementById('btnSaveBQ');

        function toFixed2(n){ n=Number(n||0); return Math.round(n*100)/100; }

        $btn.addEventListener('click', function(){
          // kumpulkan vendors untuk header (id + nama saja sudah cukup)
          const vHeader = vendors.slice(0,6).map(v => ({
            id: v.id, name: v.name
          }));

          // kumpulkan detail
          const details = [];
          const tbodyRows = $form.querySelectorAll('tbody tr');
          tbodyRows.forEach((tr, rIdx) => {
            const tds = tr.children;

            const bqNo  = tds[0].textContent.trim();
            const line  = tds[1].textContent.trim();
            const descr = tds[2].textContent.trim();
            const qty   = toFixed2(tds[3].querySelector('.bq-qty').value);
            const uom   = tds[4].textContent.trim();

            const rowVendors = [];
            // kolom vendor mulai index 5
            vendors.forEach((v, i) => {
              const td = tds[5+i];
              const mat = toFixed2(td.querySelector('.bq-price-mat').value);
              const jsa = toFixed2(td.querySelector('.bq-price-jsa').value);
              rowVendors.push({
                idx: i+1,
                product_price: mat,
                jasa_price: jsa
              });
            });

            details.push({
              bq_no: bqNo,
              bq_line_no: line,
              bq_descr: descr,
              qty: qty,
              uom: uom,
              vendor: rowVendors
            });
          });

          const fd = new FormData($form);
          fd.append('vendors', JSON.stringify(vHeader));
          fd.append('details', JSON.stringify(details));

          fetch("{{ route('bqcs.store') }}", {
            method: 'POST',
            headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'},
            body: fd
          })
          .then(r => r.json())
          .then(res => {
            if(res.ok){
              alert('BQ saved: '+res.bqid);
              window.location.href = "{{ url('/cslist') }}";
            }else{
              alert('Save failed: '+(res.msg||''));
            }
          })
          .catch(err => alert('Error: '+err));
        });
      })();
  </script>
  <script>
    (function(){
      const vendors = @json($vendors);
      const VENDOR_OFFSET = 5; // kolom vendor mulai dari index-td ke 5 (0-based): BQ No, Line, Descr, Qty, UoM -> 5
      const nf = new Intl.NumberFormat('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

      function toNum(v){ const n = parseFloat(v); return isNaN(n) ? 0 : n; }
      function fmt(n){ return nf.format(toNum(n)); }

      /** hitung ulang total utk 1 vendor (vendorIdx: 1..N) */
      function recalcVendor(vendorIdx){
        let sumMat = 0, sumJsa = 0;

        document.querySelectorAll('#bqForm tbody tr').forEach(tr => {
          const tds = tr.children;
          const qtyInput = tds[3].querySelector('.bq-qty');
          const qty = toNum(qtyInput?.value || 0);

          const tdVendor = tds[VENDOR_OFFSET + (vendorIdx-1)];
          if(!tdVendor) return;

          const mat = toNum(tdVendor.querySelector('.bq-price-mat')?.value || 0);
          const jsa = toNum(tdVendor.querySelector('.bq-price-jsa')?.value || 0);

          sumMat += qty * mat;
          sumJsa += qty * jsa;
        });

        const grand = sumMat + sumJsa;

        const matEl   = document.querySelector(`.sum-mat[data-vendor="${vendorIdx}"]`);
        const jsaEl   = document.querySelector(`.sum-jsa[data-vendor="${vendorIdx}"]`);
        const grandEl = document.querySelector(`.sum-grand[data-vendor="${vendorIdx}"]`);
        if(matEl)   matEl.textContent   = fmt(sumMat);
        if(jsaEl)   jsaEl.textContent   = fmt(sumJsa);
        if(grandEl) grandEl.textContent = fmt(grand);
      }

      /** hitung ulang semua vendor */
      function recalcAllVendors(){
        for(let i=1; i<=Math.min(vendors.length, 6); i++){
          recalcVendor(i);
        }
      }

      // event: qty & price berubah -> recalc
      document.getElementById('bqForm').addEventListener('input', (e) => {
        if (e.target.matches('.bq-qty,.bq-price-mat,.bq-price-jsa')) {
          recalcAllVendors();
        }
      });

      // kalkulasi awal saat halaman siap
      document.addEventListener('DOMContentLoaded', recalcAllVendors);
      // kalau script ini dimuat setelah DOM, panggil langsung juga:
      recalcAllVendors();
    })();
  </script>

  <script>
    (function(){
      // Batasi input hanya angka + titik
      function allowOnlyDecimal(el){
        el.addEventListener('keypress', function(e){
          const char = String.fromCharCode(e.which);
          // hanya izinkan angka (0-9) dan titik (.)
          if(!/[0-9.]/.test(char)){
            e.preventDefault();
          }
        });
        el.addEventListener('input', function(e){
          // hapus semua karakter non-angka/non-titik
          this.value = this.value.replace(/[^0-9.]/g, '');
          // hanya boleh 1 titik
          const parts = this.value.split('.');
          if(parts.length > 2){
            this.value = parts[0] + '.' + parts.slice(1).join('');
          }
        });
      }

      // pasang ke semua field qty & price
      document.querySelectorAll('.bq-qty,.bq-price-mat,.bq-price-jsa').forEach(el => {
        allowOnlyDecimal(el);
      });
    })();
  </script>

  <script>
    (function(){
      const selector = '.bq-qty,.bq-price-mat,.bq-price-jsa';

      // Izinkan tombol kontrol
      const CTRL_KEYS = new Set(['Backspace','Delete','Tab','ArrowLeft','ArrowRight','Home','End']);

      // Cegah input tidak valid di keydown
      document.addEventListener('keydown', function(e){
        if (!e.target.matches(selector)) return;

        const key = e.key;

        // izinkan tombol kontrol
        if (CTRL_KEYS.has(key)) return;

        // blokir e/E/+/- (notasi scientific & tanda)
        if (key === 'e' || key === 'E' || key === '+' || key === '-') {
          e.preventDefault();
          return;
        }

        // angka OK
        if (key >= '0' && key <= '9') return;

        // titik desimal: hanya boleh satu
        if (key === '.') {
          const v = e.target.value || '';
          if (v.includes('.')) e.preventDefault();
          return;
        }

        // selain itu -> blok
        e.preventDefault();
      });

      // Sanitasi saat input (hapus selain digit/titik, merge >1 titik jadi satu)
      document.addEventListener('input', function(e){
        if (!e.target.matches(selector)) return;

        let v = e.target.value || '';
        // ganti koma → titik (kalau ada)
        v = v.replace(/,/g, '.');
        // buang karakter non angka/titik
        v = v.replace(/[^0-9.]/g, '');

        // pastikan hanya 1 titik
        const parts = v.split('.');
        if (parts.length > 2) {
          v = parts[0] + '.' + parts.slice(1).join('');
        }
        e.target.value = v;
      });

      // Format ke 2 desimal saat blur; kosong → 0.00
      document.addEventListener('blur', function(e){
        if (!e.target.matches(selector)) return;

        const raw = e.target.value.trim();
        const num = parseFloat(raw === '' ? '0' : raw);
        const fixed = isNaN(num) ? '0.00' : num.toFixed(2);
        e.target.value = fixed;

        // panggil kalkulasi ulang grand total (fungsi milikmu)
        try {
          // kalau fungsi recalcAllVendors ada, panggil
          if (typeof recalcAllVendors === 'function') recalcAllVendors();
        } catch(_) {}
      }, true);

      // Inisialisasi default value 0.00 bila ada input kosong saat load
      document.querySelectorAll(selector).forEach(el => {
        if (el.value.trim() === '') el.value = '0.00';
      });
    })();
  </script>



</x-app-layout>
