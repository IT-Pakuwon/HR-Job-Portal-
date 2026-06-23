@extends('layouts.template')
@section('header_scripts')

<meta name="csrf-token" content="{{ csrf_token() }}">


<style>
    .loading-spinner {
        border: 3px solid #f3f3f3;
        border-radius: 50%;
        border-top: 3px solid #3498db;
        width: 15px;
        height: 15px;
        -webkit-animation: spin 1s linear infinite;
        animation: spin 1s linear infinite;
        display: inline-block;
        vertical-align: middle;
    }

    @-webkit-keyframes spin {
        0% { -webkit-transform: rotate(0deg); }
        100% { -webkit-transform: rotate(360deg); }
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
</style>


@endsection
@section('content')
<div class="content-wrapper" style="background-color: #051931; border-radius: 5px 0 0 0; margin-top:3rem; padding-left:2rem; padding-right:2rem">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1 style="color: white">Create Receive</h1>
        <ol class="breadcrumb" style="background: transparent;">
            <li><a href="#" style="color: white;font-size:13px"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active" style="color: white;font-size:13px">Create Receive</li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="box box-info" style=" border-radius:5px; background-color:white; border-top:none; ">
            <!-- /.box-header -->
            <!-- form start -->
            {{-- <form class="form-horizontal" enctype="multipart/form-data" method="POST" action="{{ route('savevoucher') }}"> --}}
                {{-- {{ csrf_field() }} --}}
            <form id="inputForm" name="inputForm" class="form-horizontal" enctype="multipart/form-data">
                <div class="box-body">
                    <div class="box-body">
                        <div class="col-md-12">                            
                            <label for="inputEmail3" class="col-sm-1 control-label" ><span style="color:red;font-weight:bold"> *</span>Company</label>                            
                            <div class="col-sm-2">
                                <select class="form-control select2" name="cpnyid" style="width: 100%;" required>                                    
                                    @foreach($usercpny as $p)
                                        <option value="{{ $p->cpnyid }}" {{ $p->cpnyid == $usercpny2->cpnyid ? 'selected' : '' }}>{{ $p->cpnyid }}</option>
                                    @endforeach
                                </select>                               
                            </div>     
                            <label for="inputEmail3" class="col-sm-1 control-label" ><span style="color:red;font-weight:bold"> *</span>Department</label>                            
                            <div class="col-sm-3">
                                <select class="form-control select2" name="department" style="width: 100%;" required>                                    
                                    @foreach($userdept as $p)
                                        <option value="{{ $p->deptname }}" {{ $p->deptname == $userdept2->deptname ? 'selected' : '' }}>{{ $p->deptname }}</option>
                                    @endforeach
                                </select>                               
                            </div> 

                            <label for="inputEmail3" class="col-sm-1 control-label" ><span style="color:red;font-weight:bold"> *</span>Voucher / Product Type</label>                            
                            <div class="col-sm-3">
                                <select class="form-control select2" id="vp_type" name="vp_type" style="width: 100%;" >
                                    <option selected="selected"></option>
                                    <option value="V">Voucher</option>
                                    <option value="P">Product</option>
                                </select>                               
                            </div>

                        </div>                        
                    </div>      
                    <div class="box-body">
                        <div class="col-md-12">    
                            <label for="inputEmail3" class="col-sm-1 control-label" ><span style="color:red;font-weight:bold"> *</span>Source of Receive</label>                            
                            <div class="col-sm-2">
                                <select class="form-control select2" id="receive_type" name="receive_type" style="width: 100%;" >
                                    <option selected="selected"></option>
                                    <option value="Media Promo">Media Promo</option>
                                    <option value="Event">Event</option>
                                    <option value="Rental">Rental</option>
                                    <option value="Promo Levy">Promo Levy</option>
                                </select>                               
                            </div> 

                            <label for="inputEmail3" class="col-sm-1 control-label" ><span style="color:red;font-weight:bold"> *</span>Tenant / Event</label>                            
                            <div class="col-sm-3">
                                <select class="form-control select2" name="product_source_tenant" style="width: 100%;" required>   
                                    {{-- <option selected="selected"></option>                                 
                                    @foreach($msproduct as $p)
                                    <option value="{{ $p->product_source_tenant }}">{{ $p->product_source_tenant }}</option>
                                    @endforeach --}}
                                </select>                               
                            </div>       

                            <label for="inputEmail3" class="col-sm-1 control-label" ><span style="color:red;font-weight:bold"> *</span>Department of Receive</label>                            
                            <div class="col-sm-3">
                                <select class="form-control select2" id="source_receive_dept" name="source_receive_dept" style="width: 100%;" >
                                    <option selected="selected"></option>
                                    <option value="CASUALLEASING">CASUALLEASING</option>
                                    <option value="LEASING">LEASING</option>
                                    <option value="PROMOTION">PROMOTION</option>
                                </select>                               
                            </div>
                        </div>
                        
                    </div>

                    <div class="box-body">
                        <div class="col-md-12">    
                            <label for="inputEmail3" class="col-sm-1 control-label">Remark</label>
                            <div class="col-sm-10">
                                <textarea type="text" rows="3" class="form-control" name="receive_remark"  placeholder="Enter Remarks ..."></textarea>
                            </div>
                        
                        </div>
                    </div>

                    <hr>
                    <div class="box-body">
                        <h5 class="modal-title">Receive Details</h5><br>
                        <table class="table table-bordered" id="dynamicTable">
                            <thead>
                                <tr>
                                    <th style="width: 50%;"><span style="color:red;font-weight:bold"> *</span>Product Name</th>
                                    <th style="width: 10%;"><span style="color:red;font-weight:bold"> *</span>Qty</th>                                
                                    <th style="width: 15%;"><span style="color:red;font-weight:bold"> *</span>Expired Date</th>
                                    <th style="width: 20%;"><span style="color:red;font-weight:bold"> *</span>Destination WHS</th>
                                    <th style="width: 5%;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <select class="form-control select2" name="addmore[0][product_name]" id="addmore[0][product_name]" style="width: 100%;">
                                            <option value="">Select Product</option>
                                        </select>
                                    </td>
                                    <td><input type="text" name="addmore[0][qty]" placeholder="Enter Qty" class="form-control" /></td>                               
                                    <td><input type="date" name="addmore[0][expired_date]" class="form-control" /></td>
                                    <td>
                                        {{-- <select class="form-control select2" id="addmore[0][source_whs]" name="addmore[0][source_whs]" style="width: 100%;" >
                                            <option selected="selected"></option>
                                            @foreach($mswhs as $p)
                                                <option value="{{ $p->whs_id }}">{{ $p->whs_id }}</option>
                                            @endforeach
                                        </select> --}}
                                        <select class="form-control select2" name="addmore[0][whs_id]" id="addmore[0][whs_id]" style="width: 100%;">
                                            <option value="">Select Warehouse</option>
                                        </select>
                                    </td>
                                    <td><button type="button" name="add" id="addRow" class="btn btn-success">Add</button></td>
                                </tr>
                            </tbody>
                        </table>
                        
                    </div>
                    <hr>
                    <div class="box-body">
                        <table class="table table-bordered" id="dynamicTable2">
                            <thead>
                                <tr>
                                    <th>Attachment</th>                                           
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>                                            
                                    <td><input type="file" name="attachment[]" class="form-control" /></td>                                          
                                    <td><button type="button" name="add" id="addRow2" class="btn btn-success">Add</button></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>            
                </div>
                <!-- /.box-body -->
                <div class="box-footer">
                    <button type="submit" id="saveBUTTON" class="btn mb-1" style="background-color: #006ef0; color:white">Submit Approval</button>
                    <a href="{{ url('/vplreceive_waiting') }}" style="margin-right: 10px;background-color:#EA002F; color:white" class="btn mb-1">Cancel</a>
                   
            </form>
        </div>
    </section>
    <!-- /.content -->
</div>
@endsection
@section('footer_scripts')

<script type="text/javascript">   
    $(document).ready(function() {
        var j = 0;

        // Add new row
        $('#addRow2').click(function() {
            j++;
            $('#dynamicTable2').append('<tr id="row' + j + '"><td><input type="file" name="attachment[' + j + ']" class="form-control" /></td><td><button type="button" name="remove" id="' + j + '" class="btn btn-danger btn_remove2">Del</button></td></tr>');
        });

        // Remove row
        $(document).on('click', '.btn_remove2', function() {
            var button_id = $(this).attr("id");
            $('#row' + button_id + '').remove();
        });
    });
   
</script>

<script type="text/javascript">
    $(document).ready(function() {
        $.ajaxSetup({
          headers: {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          }
        });   

        $('#saveBUTTON').click(function(e) {
            e.preventDefault();

            // Reset pesan error sebelumnya
            $('.error-message').remove();

            // Lakukan validasi
            let isValid = validateForm();

            console.log("Is form valid?", isValid);

            // Jika validasi gagal, berhenti di sini
            if (!isValid) {
                console.log("Validation failed. Submit aborted.");
                return false;
            }

            // Simpan tombol dan teks aslinya
            var button = $(this);
            var originalText = button.html();

            // Ubah tombol menjadi loading state
            button.prop('disabled', true);
            button.html('<span class="loading-spinner"></span> Loading...');

            // Proses pengiriman data          
            var form = $('#inputForm')[0];
            var formData = new FormData(form);
            console.log(formData);
            $.ajax({
                type: 'POST',
                url: "{{ route('savevplreceive') }}", // Ubah dengan route yang sesuai
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    // Menampilkan toastr sukses
                    toastr.options = {
                        "closeButton": true,
                        "progressBar": true,
                        "positionClass": "toast-top-right", // Atur posisi sesuai kebutuhan
                    };
                    toastr.success('Receive saved successfully!');
                    
                    // Redirect setelah toastr ditampilkan
                    // setTimeout(function() {
                        window.location.href = "{{ route('vplreceive_waiting.vplreceive_waiting') }}";
                    // }, 2000); // Menunggu 2 detik sebelum redirect untuk memberi waktu toast tampil
                },

                error: function(xhr) {
                    // Menangkap respon error dari server
                    if (xhr.status === 422) {
                        // Tampilkan pesan error dari respon JSON
                        var errorMessage = xhr.responseJSON.error;
                        alert(errorMessage);
                    } else {
                        alert('Error occurred while saving. Please check your inputs.');
                    }
                },
                complete: function() {
                    // Kembalikan tombol ke keadaan semula
                    button.prop('disabled', false);
                    button.html(originalText);
                }
            });
        });
      
    });

    // Fungsi validasi form utama
    function validateForm() {
        let isValid = true;

        // Panggil masing-masing fungsi validasi
        isValid &= validateSelectField('cpnyid', 'Company is required');
        isValid &= validateSelectField('vp_type', 'Voucher / Product Type is required');
        isValid &= validateSelectField('product_source_tenant', 'Tenant is required');
        isValid &= validateSelectField('receive_type', 'Source of Receive is required');
        isValid &= validateSelectField('source_receive_dept', 'Department of Receive is required');
        isValid &= validateDynamicTable();

        return isValid;
    }

    // Fungsi validasi select input (dropdown)
    function validateSelectField(fieldName, errorMessage) {
        let field = $('select[name="' + fieldName + '"]');
        if (field.val() === '') {
            field.after('<span class="error-message" style="color: red;">' + errorMessage + '</span>');
            return false;
        }
        return true;
    }

    // Fungsi validasi untuk tabel dinamis (Receive Details)
    function validateDynamicTable() {
        var isValid = true;

        // Loop through each row in the dynamic table
        $('#dynamicTable tbody tr').each(function() {           
            var productName = $(this).find('select[name^="addmore"]').filter('[name*="[product_name]"]').val(); 
            var qty = $(this).find('input[name^="addmore"]').filter('[name*="[qty]"]').val();
            var expiredDate = $(this).find('input[name^="addmore"]').filter('[name*="[expired_date]"]').val();
            var sourceWhs = $(this).find('select[name^="addmore"]').filter('[name*="[whs_id]"]').val();

            if (!productName) {
                $(this).find('select[name^="addmore"]').filter('[name*="[product_name]"]').after('<span class="error-message" style="color: red;">Product Name is required</span>');
                isValid = false;
            }

            if (!qty) {
                $(this).find('input[name^="addmore"]').filter('[name*="[qty]"]').after('<span class="error-message" style="color: red;">Qty is required</span>');
                isValid = false;
            }

            if (!expiredDate) {
                $(this).find('input[name^="addmore"]').filter('[name*="[expired_date]"]').after('<span class="error-message" style="color: red;">Expired Date is required</span>');
                isValid = false;
            }
           
            if (!sourceWhs) {
                $(this).find('select[name^="addmore"]').filter('[name*="[whs_id]"]').after('<span class="error-message" style="color: red;">Destination WHS is required</span>');
                isValid = false;
            }
        });

        return isValid;
    }

</script>
<script>
    $(document).on('keypress', 'input[name^="addmore"][name*="[qty]"]', function(e) {
        var charCode = e.which ? e.which : e.keyCode;
        // Membatasi karakter input hanya untuk angka (0-9)
        if (charCode < 48 || charCode > 57) {
            e.preventDefault();
            return false;
        }
    });
</script>
<script>
$(document).ready(function() {
      
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });   

    loadTenantsByCpnyid();
    // Inisialisasi saat halaman pertama kali dimuat
    loadProductsForAllRows();   
    loadWhsForAllRows(); 

    var i = 0;
    $('#addRow').click(function() {
        i++;
        $('#dynamicTable').append('<tr id="row' + i + '"><td><select class="form-control select2 product-select" name="addmore[' + i + '][product_name]" data-cpnyid="" style="width: 100%;"><option value="">Select Product</option></select></td><td><input type="text" name="addmore[' + i + '][qty]" placeholder="Enter Qty" class="form-control" /></td><td><input type="date" name="addmore[' + i + '][expired_date]" class="form-control" /></td><td><select class="form-control select2 product-select" name="addmore[' + i + '][whs_id]" data-cpnyid="" style="width: 100%;"><option value="">Select Warehouse</option></select></td><td><button type="button" name="remove" id="' + i + '" class="btn btn-danger btn_remove">Del</button></td></tr>');
        // $('#dynamicTable').append('<tr id="row' + i + '"><td><select class="form-control select2 product-select" name="addmore[' + i + '][product_name]" data-cpnyid="" style="width: 100%;"><option value="">Select Product</option></select></td><td><input type="text" name="addmore[' + i + '][qty]" placeholder="Enter Qty" class="form-control" /></td><td><input type="date" name="addmore[' + i + '][expired_date]" class="form-control" /></td><td><button type="button" name="remove" id="' + i + '" class="btn btn-danger btn_remove">Del</button></td></tr>');
    
    $('.select2').select2();
    loadProductsForRow(i);
    loadWhsForRow(i);
    
    });

       

    // Fungsi untuk menghapus baris dinamis
    $(document).on('click', '.btn_remove', function() {
        var button_id = $(this).attr("id");
        $('#row' + button_id + '').remove();
    });

    // Fungsi untuk memuat produk saat cpnyid dipilih (saat pertama kali load halaman atau ketika select cpnyid berubah)
    function loadProductsForAllRows() {
        var cpnyid = $('select[name="cpnyid"]').val();
        var product_source_tenant = $('select[name="product_source_tenant"]').val();
        var vp_type = $('select[name="vp_type"]').val();

        if (cpnyid) {
            $.ajax({
                url: "{{ route('get-products-receive') }}",
                type: "POST",
                data: {
                    cpnyid: cpnyid,
                    vp_type: vp_type,
                    product_source_tenant: product_source_tenant,
                    _token: '{{ csrf_token() }}'
                },
                success: function(data) {
                    $('select[name^="addmore"][name*="[product_name]"]').each(function() {
                        $(this).empty();
                        $(this).append('<option value="">Select Product</option>');
                        $.each(data, function(key, value) {
                            $(this).append('<option value="' + value.product_id + '">' + value.product_name + '</option>');
                            // $(this).append('<option value="' + value.product_id + '">' + value.product_name + ' - Qty: ' + value.qty_available + '</option>');
                        }.bind(this));
                    });
                },
                error: function(xhr) {
                    console.log(xhr);
                }
            });
        }
    }

    // Fungsi untuk memuat produk hanya untuk baris yang baru ditambahkan
    function loadProductsForRow(rowIndex) {
        var cpnyid = $('select[name="cpnyid"]').val();
        var vp_type = $('select[name="vp_type"]').val();
        var product_source_tenant = $('select[name="product_source_tenant"]').val();

        if (cpnyid) {
            $.ajax({
                url: "{{ route('get-products-receive') }}",
                type: "POST",
                data: {
                    cpnyid: cpnyid,
                    vp_type: vp_type,
                    product_source_tenant: product_source_tenant,
                    _token: '{{ csrf_token() }}'
                },
                success: function(data) {
                    $('select[name="addmore[' + rowIndex + '][product_name]"]').empty();
                    $('select[name="addmore[' + rowIndex + '][product_name]"]').append('<option value="">Select Product</option>');
                    $.each(data, function(key, value) {
                        $('select[name="addmore[' + rowIndex + '][product_name]"]').append('<option value="' + value.product_id + '">' + value.product_name + '</option>');
                    });
                },
                error: function(xhr) {
                    console.log(xhr);
                }
            });
        }
    }
   
    // Fungsi untuk memuat produk hanya untuk baris yang baru ditambahkan
    function loadWhsForRow(rowIndex) {
        var cpnyid = $('select[name="cpnyid"]').val();
        var department = $('select[name="department"]').val();
        var vp_type = $('select[name="vp_type"]').val();
        if (cpnyid) {
            $.ajax({
                url: "{{ route('get-warehouse') }}",
                type: "POST",
                data: {
                    cpnyid: cpnyid,
                    department: department,
                    vp_type: vp_type,
                    _token: '{{ csrf_token() }}'
                },
                success: function(data) {
                    $('select[name="addmore[' + rowIndex + '][whs_id]"]').empty();
                    $('select[name="addmore[' + rowIndex + '][whs_id]"]').append('<option value="">Select Warehouse</option>');
                    $.each(data, function(key, value) {
                        $('select[name="addmore[' + rowIndex + '][whs_id]"]').append('<option value="' + value.whs_id + '">' + value.whs_id + '</option>');
                    });
                },
                error: function(xhr) {
                    console.log(xhr);
                }
            });
        }
    }

    // Fungsi untuk memuat produk saat cpnyid dipilih (saat pertama kali load halaman atau ketika select cpnyid berubah)
    function loadWhsForAllRows() {
        var cpnyid = $('select[name="cpnyid"]').val();
        var department = $('select[name="department"]').val();
        var vp_type = $('select[name="vp_type"]').val();
        if (cpnyid) {
            $.ajax({
                url: "{{ route('get-warehouse') }}",
                type: "POST",
                data: {
                    cpnyid: cpnyid,
                    department: department,
                    vp_type: vp_type,
                    _token: '{{ csrf_token() }}'
                },
                success: function(data) {
                    $('select[name^="addmore"][name*="[whs_id]"]').each(function() {
                        $(this).empty();
                        $(this).append('<option value="">Select Warehouse</option>');
                        $.each(data, function(key, value) {
                            $(this).append('<option value="' + value.whs_id + '">' + value.whs_id + '</option>');                            
                        }.bind(this));
                    });
                },
                error: function(xhr) {
                    console.log(xhr);
                }
            });
        }
    }

    // Event handler saat cpnyid berubah, muat ulang produk untuk semua baris
    $('select[name="cpnyid"]').change(function() {
        loadProductsForAllRows();
        loadTenantsByCpnyid();
        resetProducts();
        loadWhsForAllRows();
    });

    $('select[name="department"]').change(function() {
        // loadProductsForAllRows();
        loadTenantsByCpnyid();
        resetProducts();
        loadWhsForAllRows();
    });
    $('select[name="vp_type"]').change(function() {
        loadProductsForAllRows();
        loadTenantsByCpnyid();
        resetProducts();
        loadWhsForAllRows();
    });
    $('select[name="product_source_tenant"]').change(function() {
        loadProductsForAllRows();
        loadWhsForAllRows();
    });

    function loadTenantsByCpnyid() {
        var cpnyid = $('select[name="cpnyid"]').val();
        console.log(cpnyid)
        if (cpnyid) {
            $.ajax({
                url: "{{ route('get-tenants-by-cpnyid') }}",
                type: "POST",
                data: {
                    cpnyid: cpnyid,
                    _token: '{{ csrf_token() }}',
                },
                success: function (data) {
                    var tenantSelect = $('select[name="product_source_tenant"]');
                    tenantSelect.empty();
                    tenantSelect.append('<option value="">Select Tenant</option>');

                    // Loop dan tambahkan data tenant ke dropdown
                    $.each(data, function (key, value) {
                        tenantSelect.append('<option value="' + value.product_source_tenant + '">' + value.product_source_tenant + '</option>');
                    });
                },
                error: function (xhr) {
                    console.log(xhr);
                    alert('Failed to load tenants. Please try again.');
                },
            });
        }
    }

    function resetProducts() {
        // Kosongkan dropdown product_name saat cpnyid berubah
        $('select[name^="addmore"][name*="[product_name]"]').empty();
        $('select[name^="addmore"][name*="[product_name]"]').append('<option value="">Select Product</option>');
    }

    $(document).on('change', 'select[name^="addmore"][name*="[product_name]"]', function () {
        var selectedProductId = $(this).val();
        var currentRow = $(this).closest('tr');

        if (selectedProductId) {
            $.ajax({
                url: "{{ route('get-product-details') }}", // Tambahkan route baru
                type: "POST",
                data: {
                    product_id: selectedProductId,
                    _token: '{{ csrf_token() }}'
                },
                success: function (data) {
                    if (data.product_check_exp === 0) {
                        // Set expired_date menjadi 1900-01-01 dan disable field
                        currentRow.find('input[name^="addmore"][name*="[expired_date]"]').val('1900-01-01').prop('disabled', true);
                    } else {
                        // Reset jika produk memungkinkan expired_date
                        currentRow.find('input[name^="addmore"][name*="[expired_date]"]').val('').prop('disabled', false);
                    }
                },
                error: function () {
                    alert('Failed to load product details');
                }
            });
        }
    });

    
});
</script>



</script>



@endsection
