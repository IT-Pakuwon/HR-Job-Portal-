@extends('layouts.template')
@section('header_scripts')

<style>
    table {
        border-collapse: collapse;
        width: 100%;
        font-family: Arial, sans-serif;
    }

    th, td {
        border: 1px solid black;
        text-align: center;
        padding: 8px;
    }

    th {
        background-color: #3C3F41;
        color: white;
    }

    .sub-header {
        background-color: #3C3F41;
        color: white;
        font-weight: bold;
    }
</style>

@endsection

@section('content')

<div class="content-wrapper" style="background-color: #051931;">
  <section class="content-header">
    <h1 style="color: white">Posting</h1>
      <ol class="breadcrumb">
          <li style="color: white; font-size:13px" ><a href="#" style="color: white"><i class="fa fa-dashboard"></i> Home</a></li>
          <li class="active" style="color: white;font-size:13px">{{$tittle}}</li>
      </ol>
  </section>
          
  
    <!-- Main content -->
    <section class="content" >  
      <div class="box box-info" style="border-top:none; border-radius:10px;">      
                <div class="box-header" style="border-top: none; border-radius: 10px;">
                    <div class="row">                       
                        <div class="col-md-3">
                            <label for="cpnyid" class="control-label">Company:</label>
                            <select class="form-control select2" id="cpnyid" name="cpnyid">
                                <option selected="selected" value=""></option>
                               @foreach($company as $p)
                                    <option value="{{ $p->cpnyid }}">{{ $p->cpnyid }}</option>
                                @endforeach
                            </select>
                        </div>                
                        <div class="col-md-3">
                            <label for="cpnyid" class="control-label">Year:</label>
                            <select class="form-control select2" id="year" name="year">
                                <option selected="selected" value=""></option>
                                @foreach($year as $p)
                                    <option value="{{ $p->perpost_year }}">{{ $p->perpost_year }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="cpnyid" class="control-label">Month:</label>
                            <select class="form-control select2" id="month" name="month">
                                <option selected="selected" value=""></option>
                                @foreach($month as $p)
                                    <option value="{{ $p->perpost_month }}">{{ $p->perpost_month }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                       
                <div class="box-body">
                    <table class="table table-bordered data-table">
                        <thead>
                            <tr>
                                <th>Company</th>
                                <th>Year</th>
                                <th>Month</th>
                                <th>Status</th>
                                <th>Action</th>                                                            
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
<!-- Tambahkan SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script type="text/javascript">
    $(function () {
        var previousMonths = {}; // Simpan data bulan sebelumnya berdasarkan cpnyid

        // Fetch Latest Active Month
        $.ajax({
            url: "{{ route('latest_active_month') }}",
            type: "GET",
            dataType: "json",
            success: function (data) {
                console.log("Latest Active Months Response:", data); // DEBUG 1: Cek hasil AJAX
                if (data && Object.keys(data).length > 0) {
                    previousMonths = data;
                    console.log("Processed Previous Months:", previousMonths); // DEBUG 2: Cek hasil parsing
                } else {
                    console.warn("No active month data received.");
                }

                loadDataTable();
            },
            error: function () {
                console.error("Failed to fetch latest active month.");
                loadDataTable(); // Tetap load table meskipun error
            }
        });


        function loadDataTable() {
            var table = $('.data-table').DataTable({
                "responsive": true,
                "autoWidth": false,
                processing: true,
                serverSide: true,
                destroy: true,
                ajax: {
                    url: "{{ route('posting_periode.posting_periode') }}",
                    data: function (d) {
                        d.cpnyid = $('#cpnyid').val();
                        d.year = $('#year').val();
                        d.month = $('#month').val();
                    }
                },
                columns: [
                    { data: 'cpnyid', name: 'cpnyid' },
                    { data: 'perpost_year', name: 'perpost_year' },
                    { data: 'perpost_month', name: 'perpost_month' },
                    { data: 'statusx', name: 'statusx', orderable: false, searchable: false },
                    {
                        data: 'action', name: 'action', orderable: false, searchable: false, render: function (data, type, row) {
                            var cpnyid = row.cpnyid;
                            var rowPeriod = row.perpost_year + ("0" + row.perpost_month).slice(-2); // Format YYYYMM

                            var previousMonthData = previousMonths.find(item => item.cpnyid === cpnyid); // Gunakan find()
                            var previousPeriod = previousMonthData ? previousMonthData.year + previousMonthData.month : null;

                            console.log(`CPNY: ${cpnyid}, Row: ${rowPeriod}, Prev: ${previousPeriod}, Status: ${row.status}`);

                            if (row.status === 'A' || rowPeriod === previousPeriod) {
                                return `<button class="btn btn-primary editProcess" data-id="${row.id}">Process</button>`;
                            } else {
                                return '';
                            }
                        }
                    }
                ],
                dom: 'lBfrtip',
                buttons: ['excel', 'csv', 'pdf', 'copy'],
                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
                responsive: true,
            });

            // Event listener untuk filter
            $('#cpnyid, #year, #month').change(function () {
                table.ajax.reload();
            });

            // Event listener untuk tombol Process
            $(document).on('click', '.editProcess', function () {
                var id = $(this).data('id');

                Swal.fire({
                    title: "Konfirmasi Proses?",
                    text: "Apakah Anda yakin ingin Posting periode ini?",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#007bff",
                    cancelButtonColor: "#d33",
                    confirmButtonText: "Ya, Proses!",
                    cancelButtonText: "Batal"
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ route('posting_process') }}",
                            type: "POST",
                            data: { _token: "{{ csrf_token() }}", id: id },
                            beforeSend: function () {
                                Swal.fire({
                                    title: "Processing...",
                                    text: "Harap tunggu, sedang diproses.",
                                    allowOutsideClick: false,
                                    showConfirmButton: false,
                                    didOpen: () => {
                                        Swal.showLoading();
                                    }
                                });
                            },
                            success: function (response) {
                                Swal.fire({
                                    title: "Sukses!",
                                    text: "Proses Posting berhasil diproses.",
                                    icon: "success",
                                    confirmButtonText: "OK"
                                });
                                table.ajax.reload();
                            },
                            error: function (xhr) {
                                Swal.fire({
                                    title: "Error!",
                                    text: "Terjadi kesalahan saat memproses.",
                                    icon: "error",
                                    confirmButtonText: "OK"
                                });
                            }
                        });
                    }
                });
            });
        }
    });

</script>
@endsection


