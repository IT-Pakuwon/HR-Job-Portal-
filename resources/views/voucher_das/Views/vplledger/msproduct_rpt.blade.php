@extends('layouts.template')

@section('content')

<div class="content-wrapper" style="background-color: #051931;">
  <section class="content-header">
    <h1 style="color: white">Report</h1>
      <ol class="breadcrumb">
          <li style="color: white; font-size:13px" ><a href="#" style="color: white"><i class="fa fa-dashboard"></i> Home</a></li>
          <li class="active" style="color: white;font-size:13px">{{$tittle}}</li>
      </ol>
  </section>
          
  
    <!-- Main content -->
    <section class="content" >  
      <div class="box box-info" style="border-top:none; border-radius:10px;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h1 style="color: Black; padding-left:2rem; font-size:2rem">{{$tittle}}</h1>
        </div>              
            <div class="box-body">
                <table class="table table-bordered data-table">
                    <thead>
                      <tr>
                        <th>Company</th>
                        <th>Product ID</th>
                        <th>Expired Date</th>                                            
                        <th>Name</th>   
                        <th>Value</th>    
                        <th>Uom</th>                                                                        
                        <th>Warehouse</th>  
                        <th>Stock</th>
                    </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>  
        </div>
    </section>
</div>

@endsection

@section('footer_scripts')

<script type="text/javascript">
    $(function () {
        $('.data-table thead tr').clone(true).appendTo('.data-table thead');
    
        // Ambil semua data untuk filter di luar DataTables
        $.ajax({
            url: "{{ route('msproduct_rpt.msproduct_rpt') }}", // Ganti dengan route yang sesuai
            type: "GET",
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function (data) {
                var allData = data.data; // Simpan semua data dari server
    
                // Inisialisasi DataTable
                var table = $('.data-table').DataTable({
                    "order": [[1, "desc"]],
                    "responsive": true,
                    "autoWidth": false,
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: "{{ route('msproduct_rpt.msproduct_rpt') }}",
                        type: "GET",
                        data: {
                            _token: '{{ csrf_token() }}'
                        }
                    },
                    columns: [
                        {data: 'cpnyid', name: 'cpnyid'},
                        {data: 'product_id', name: 'product_id'},
                        {data: 'expired_date', name: 'expired_date'},
                        {data: 'product_name', name: 'product_name'},
                        {data: 'product_value', name: 'product_value'},
                        {data: 'product_uom', name: 'product_uom'},
                        {data: 'whs_id', name: 'whs_id'},
                        {data: 'qty_available', name: 'qty_available'},
                    ],
                    dom: 'lBfrtip',
                    buttons: ['excel', 'csv', 'pdf', 'copy'],
                    lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
                    initComplete: function () {
                        // Tambahkan filter Select2
                        this.api().columns().every(function () {
                            var column = this;
                            var select = $('<select class="form-control select2" style="width: 100%;"><option value="">Filter</option></select>')
                                .appendTo($(column.header()).empty()) // Kosongkan kolom dan tambahkan filter
                                .on('change', function () {
                                    var val = $.fn.dataTable.util.escapeRegex($(this).val());
                                    column
                                        .search(val ? '^' + val + '$' : '', true, false)
                                        .draw();
                                });
    
                            // Ambil data unik dari seluruh dataset (bukan hanya 10 baris pertama)
                            var uniqueData = allData.map(function (row) {
                                return row[column.dataSrc()]; // Ambil nilai dari kolom tertentu
                            }).filter(function (value, index, self) {
                                return self.indexOf(value) === index && value; // Hapus duplikasi dan nilai kosong
                            }).sort();
    
                            // Tambahkan opsi ke dropdown
                            uniqueData.forEach(function (d) {
                                select.append('<option value="' + d + '">' + d + '</option>');
                            });
    
                            // Inisialisasi Select2
                            select.select2({
                                placeholder: 'Filter',
                                allowClear: true
                            });
                        });
                    }
                });
            },
            error: function (xhr) {
                console.log(xhr);
                alert('Failed to load data for filters. Please try again.');
            }
        });
    });
    </script>
    
  

@endsection

