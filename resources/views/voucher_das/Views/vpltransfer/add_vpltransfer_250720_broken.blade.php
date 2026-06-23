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
        <h1 style="color: white">Create Transfer</h1>
        <ol class="breadcrumb" style="background: transparent;">
            <li><a href="#" style="color: white;font-size:13px"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active" style="color: white;font-size:13px">Create Transfer</li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="box box-info" style=" border-radius:5px; background-color:white; border-top:none; ">
           
            <form id="inputForm" name="inputForm" class="form-horizontal" enctype="multipart/form-data">
                <div class="box-body">
                    <div class="box-body">
                        <div class="col-md-12">                            
                            <label for="inputEmail3" class="col-sm-1 control-label" ><span style="color:red;font-weight:bold"> *</span>Company</label>                            
                            <div class="col-sm-1">
                                <select class="form-control select2" name="cpnyid" style="width: 100%;" required>                                    
                                    @foreach($usercpny as $p)
                                        <option value="{{ $p->cpnyid }}" {{ $p->cpnyid == $usercpny2->cpnyid ? 'selected' : '' }}>{{ $p->cpnyid }}</option>
                                    @endforeach
                                </select>                               
                            </div>     
                            <label for="inputEmail3" class="col-sm-1 control-label" ><span style="color:red;font-weight:bold"> *</span>Department</label>                            
                            <div class="col-sm-2">
                                <select class="form-control select2" name="department" style="width: 100%;" required>                                    
                                    @foreach($userdept as $p)
                                        <option value="{{ $p->deptname }}" {{ $p->deptname == $userdept2->deptname ? 'selected' : '' }}>{{ $p->deptname }}</option>
                                    @endforeach
                                </select>                               
                            </div> 
                            <label for="inputEmail3" class="col-sm-1 control-label" ><span style="color:red;font-weight:bold"> *</span>Type</label>                            
                            <div class="col-sm-2">
                                <select class="form-control select2" id="transfertype" name="transfertype" style="width: 100%;" >
                                    <option selected="selected"></option>
                                    <option value="Transfer">Transfer</option>
                                    <option value="ReturnTf">Return</option>
                                </select>                               
                            </div>     
                            <label for="inputEmail3" class="col-sm-1 control-label" ><span style="color:red;font-weight:bold"> *</span>Reference</label>                            
                            <div class="col-sm-2">
                                <select class="form-control select2" name="ref_transfer_id" id="ref_transfer_id" style="width: 100%;" required>                                    
                                    @foreach($vpltransfer as $p)
                                        <option value="{{ $p->transfer_id }}">{{ $p->transfer_id }}</option>
                                    @endforeach
                                </select>                               
                            </div>   
                        </div>                        
                    </div>      
                    <div class="box-body">
                        <div class="col-md-12">  
                            <label for="inputEmail3" class="col-sm-1 control-label">Remark</label>
                            <div class="col-sm-4">
                                <textarea type="text" rows="3" class="form-control" name="transfer_remark"  placeholder="Enter Remarks ..."></textarea>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="box-body">
                        <h5 class="modal-title">Transfer Details</h5><br>
                        <table class="table table-bordered" id="dynamicTable">
                            <thead>
                                <tr>
                                    {{-- <th style="width: 15%;">From WHS</th>
                                    <th style="width: 40%;"><span style="color:red;font-weight:bold"> *</span>Product Name</th>
                                    <th style="width: 8%;"><span style="color:red;font-weight:bold"> *</span>Stock</th>                                
                                    <th style="width: 10%;"><span style="color:red;font-weight:bold"> *</span>Expired Date</th>                                    
                                    <th style="width: 10%;"><span style="color:red;font-weight:bold"> *</span>Qty</th>
                                    <th style="width: 15%;"><span style="color:red;font-weight:bold"> *</span>To WHS</th>
                                    <th style="width: 5%;">Action</th> --}}
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>                                        
                                        <input type="text" name="addmore[0][from_whs_id]" class="form-control" id="from_whs_id_0" readonly />
                                    </td>
                                    <td>
                                        <div class="input-group">
                                            <input type="hidden" name="addmore[0][product_id]" placeholder="Enter Product Name" class="form-control" id="product_id_0" readonly/>
                                            <input type="text" name="addmore[0][product_name]" placeholder="Enter Product Name" class="form-control" id="product_name_0" readonly/>
                                            <span class="input-group-btn">
                                                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#productModal_0">
                                                    <i class="fa fa-search"></i>
                                                </button>
                                            </span>
                                        </div>
                                    </td>
                                    <td><input type="text" name="addmore[0][qty_available]" placeholder="Enter Stock" class="form-control" id="qty_available_0" readonly /></td>                               
                                    <td><input type="date" name="addmore[0][expired_date]" class="form-control" id="expired_date_0" readonly /></td>    
                                    <td><input type="text" name="addmore[0][qty_transfer]" placeholder="Enter Qty" class="form-control" value="0"  /></td>   
                                    <td>
                                        <select class="form-control select2" id="addmore[0][to_whs_id]" name="addmore[0][to_whs_id]" style="width: 100%;">
                                       
                                        </select>
                                    </td>
                                    <td><button type="button" name="add" id="addRow" class="btn btn-success">Add</button></td>
                                </tr>
                            </tbody>
                        </table>
                        
                        
                    </div>
                    <!-- Modal for selecting product -->
                    <div class="modal fade" id="productModal_0" tabindex="-1" role="dialog" aria-labelledby="productModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                <h5 class="modal-title" id="productModalLabel">Select Product</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                                </div>
                                <div class="modal-body">                                
                                <br>  
                                    <table id="example_0" class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>Product ID</th>
                                                <th>Product Name</th>
                                                <th>Stock</th>
                                                <th>Qty Transfer</th>
                                                <th>Expired Date</th>
                                                <th>WHS ID</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                           
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
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
                    <a href="{{ url('/vpltransfer_waiting') }}" style="margin-right: 10px;background-color:#EA002F; color:white" class="btn mb-1">Cancel</a>
                   
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

            // Jika ada input yang kosong, hentikan proses pengiriman
            if (!isValid) {
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
                url: "{{ route('savevpltransfer') }}", // Ubah dengan route yang sesuai
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
                    toastr.success('Transfer saved successfully!');
                    
                    // Redirect setelah toastr ditampilkan
                    // setTimeout(function() {
                        window.location.href = "{{ route('vpltransfer_waiting.vpltransfer_waiting') }}";
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
        isValid &= validateSelectField('department', 'Tenant is required');
        isValid &= validateSelectField('transfertype', 'Source of Transfer is required');        
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

   // Validation logic for the dynamic table
    function validateDynamicTable() {
        var isValid = true;

        // Loop through each row in the dynamic table
        $('#dynamicTable tbody tr').each(function() {           
            var productNameField = $(this).find('input[name^="addmore"]').filter('[name*="[product_name]"]');          
            var qtyAvailableField = $(this).find('input[name^="addmore"]').filter('[name*="[qty_available]"]');
            var qtyTransferField = $(this).find('input[name^="addmore"]').filter('[name*="[qty_transfer]"]');
            var toWhsIdField = $(this).find('select[name^="addmore"]').filter('[name*="[to_whs_id]"]');

            var productName = productNameField.val();           
            var qtyAvailable = parseFloat(qtyAvailableField.val()) || 0;
            var qtyTransfer = parseFloat(qtyTransferField.val()) || 0
            var toWhsId = toWhsIdField.val();

            // Validate product_name field
            if (!productName || productName.trim() === '') {
                productNameField.after('<span class="error-message" style="color: red;">Product Name is required</span>');
                isValid = false;
            }

          
            if (!qtyTransfer || qtyTransfer <= 0) {
                qtyTransferField.after('<span class="error-message" style="color: red;">Qty Transfer is required and must be greater than 0</span>');
                isValid = false;
            } else if (qtyTransfer > qtyAvailable) {
                qtyTransferField.after('<span class="error-message" style="color: red;">Qty Transfer cannot be greater than available stock</span>');
                isValid = false;
            }

            // Validate to_whs_id field
            if (!toWhsId || toWhsId.trim() === '') {
                toWhsIdField.after('<span class="error-message" style="color: red;">To WHS is required</span>');
                isValid = false;
            }
        });

        return isValid;
    }


</script>
<script>
    $(document).ready(function() {
        // Fungsi untuk menampilkan/menyembunyikan ref_transfer_id
        function toggleReferenceField() {
            var transferType = $('#transfertype').val();
            if (transferType === "ReturnTf") {
                $('label[for="inputEmail3"]').filter(function() {
                    return $(this).text().includes("Reference");
                }).show();
                $('select[name="ref_transfer_id"]').closest('.col-sm-2').show();
            } else {
                $('label[for="inputEmail3"]').filter(function() {
                    return $(this).text().includes("Reference");
                }).hide();
                $('select[name="ref_transfer_id"]').closest('.col-sm-2').hide();
            }
        }

        // Panggil fungsi saat halaman dimuat
        toggleReferenceField();

        // Panggil fungsi setiap kali transfertype berubah
        $('#transfertype').on('change', function() {
            toggleReferenceField();
        });
    });


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
        var i = 0;
        var transfertype = 'ReturnTf'; // Ambil nilai transfertype setelah reset
        var cpnyid = $('select[name="cpnyid"]').val();
        var warehouseId = $('#from_whs_id_0').val();
        var refTtransfer = $('#ref_transfer_id').val();

        HeaderTable(transfertype);
               
        console.log(cpnyid,'-',transfertype);
        loadFromWhsOptions(cpnyid,transfertype,0);
        // Fungsi umum untuk memuat data produk berdasarkan cpnyid dan transfertype
        function loadProducts(cpnyid,transfertype,warehouseId, refTtransfer,rowIndex) {
            $.ajax({
                url: "{{ route('getProductsByTransferType') }}",
                type: "GET",
                data: {
                    cpnyid: cpnyid,
                    transfertype: transfertype,                   
                    warehouseId:warehouseId,
                    refTtransfer:refTtransfer,
                },
                success: function(data) {
                    console.log('Received Data:', data); // Debug data yang diterima
                    reloadDataTable(rowIndex, data);
                    // Update tabel modal dengan data yang diterima
                    var tableBody = $(`#example_${rowIndex} tbody`);
                    tableBody.empty(); // Kosongkan tabel

                    // Loop data dan tambahkan ke dalam tabel modal
                    data.forEach(function(product) {
                        tableBody.append(`
                            <tr>
                                <td>${product.product_id}</td>
                                <td>${product.product_name}</td>
                                <td>${product.qty_available}</td>
                                <td>${product.qty_transfer}</td>
                                <td>${product.expired_date}</td>
                                <td>${product.whs_id}</td>
                                <td>
                                    <button type="button" class="btn btn-primary select-product"
                                        data-product-id="${product.product_id}"
                                        data-product-name="${product.product_name}"
                                        data-qty="${product.qty_available}"
                                        data-expired-date="${product.expired_date}"
                                        data-whs-id="${product.whs_id}"
                                        data-row-index="${rowIndex}" 
                                        data-dismiss="modal">Select</button>
                                </td>
                            </tr>
                        `);
                    });

                    // Re-initialize the DataTable
                    $(`#example_${rowIndex}`).DataTable();
                   

                },
                error: function() {
                    alert("Failed to load products. Please try again.");
                }
            });
        }

     
        // Event listener saat cpnyid atau transfertype berubah
        $('select[name="cpnyid"]').on('change', function() {          
            $('#transfertype').val('').trigger('change'); // Reset dan trigger event change
            $('#ref_transfer_id').val('').trigger('change');

            var transfertype = $('#transfertype').val(); // Ambil nilai transfertype setelah reset
            var cpnyid = $('select[name="cpnyid"]').val(); // Ambil nilai cpnyid
            var refTtransfer = $('#ref_transfer_id').val();
            console.log('transfertype',transfertype);            
                        
        });

        function HeaderTable(transfertype){
            const tableHeader = $('#dynamicTable thead tr');
            tableHeader.empty(); // Kosongkan header tabel

            if (transfertype === 'Transfer') {
                tableHeader.append(`
                    <th style="width: 15%;">From WHS</th>
                    <th style="width: 40%;"><span style="color:red;font-weight:bold"> *</span>Product Name</th>
                    <th style="width: 8%;"><span style="color:red;font-weight:bold"> *</span>Stock</th>                                
                    <th style="width: 10%;"><span style="color:red;font-weight:bold"> *</span>Expired Date</th>                                    
                    <th style="width: 10%;"><span style="color:red;font-weight:bold"> *</span>Qty Transfer</th>
                    <th style="width: 15%;"><span style="color:red;font-weight:bold"> *</span>To WHS</th>
                    <th style="width: 5%;">Action</th>
                `);
            } else {
                tableHeader.append(`
                    <th style="width: 15%;">From WHS</th>
                    <th style="width: 40%;"><span style="color:red;font-weight:bold"> *</span>Product Name</th>
                    <th style="width: 8%;"><span style="color:red;font-weight:bold"> *</span>Qty Transfer</th>                                
                    <th style="width: 10%;"><span style="color:red;font-weight:bold"> *</span>Expired Date</th>                                    
                    <th style="width: 10%;"><span style="color:red;font-weight:bold"> *</span>Qty Return</th>
                    <th style="width: 15%;"><span style="color:red;font-weight:bold"> *</span>To WHS</th>
                    <th style="width: 5%;">Action</th>
                `);
            }
            
        }

        $('#transfertype').on('change', function() {
            var transfertype = $(this).val();
            var cpnyid = $('select[name="cpnyid"]').val();
            var refTtransfer = $('#ref_transfer_id').val();

            HeaderTable(transfertype);

            if (transfertype) {
                console.log("Fetching From WHS for:", cpnyid, transfertype);

                // **Pastikan warehouseId diperbarui sebelum memanggil loadProducts**
                loadFromWhsOptions(cpnyid, transfertype, 0, function(warehouseId) {
                    console.log("Using updated warehouseId:", warehouseId);
                    loadProducts(cpnyid, transfertype, warehouseId,refTtransfer, 0);
                    loadToWhsOptions(cpnyid, transfertype,warehouseId,0);
                });
            }
            
            

        });

        $('#ref_transfer_id').on('change', function() {
            var refTtransfer = $(this).val();
            var cpnyid = $('select[name="cpnyid"]').val();
            var transfertype = $('#transfertype').val();

            if (refTtransfer) {
                console.log("Fetching From WHS for:", cpnyid, refTtransfer);

                // **Pastikan warehouseId diperbarui sebelum memanggil loadProducts**
                loadFromWhsOptions(cpnyid, transfertype, 0, function(warehouseId) {
                    console.log("Using updated warehouseId:", warehouseId);
                    loadProducts(cpnyid, transfertype, warehouseId,refTtransfer, 0);
                    loadToWhsOptions(cpnyid, transfertype,warehouseId,0);
                });
            }
        });

       

        function loadToWhsOptions(cpnyid, transfertype, warehouseId,rowIndex) {
            // Pastikan cpnyid dan transfertype tidak kosong
            if (!cpnyid || !transfertype) {
                return;
            }

            $.ajax({
                url: "{{ route('getToWhsOptionsTransfer') }}", // Route ke controller untuk query Mswhsdept
                type: "GET",
                data: {
                    cpnyid: cpnyid,
                    transfertype: transfertype, 
                    warehouseId: warehouseId                  
                },
                success: function (response) {
                    // Perbarui opsi di select to_whs_id untuk baris yang sesuai
                    var toWhsSelect = $(`select[name='addmore[${rowIndex}][to_whs_id]']`);
                    toWhsSelect.empty(); // Kosongkan opsi sebelumnya

                    // Tambahkan opsi baru dari response
                    toWhsSelect.append('<option value="">Select WHS</option>');
                    response.forEach(function (whs) {
                        toWhsSelect.append(`<option value="${whs.whs_id}">${whs.whs_id}</option>`);
                    });
                },
                error: function () {
                    alert("Failed to load warehouses. Please try again.");
                }
            });
        }

        function loadFromWhsOptions(cpnyid, transfertype, department, rowIndex, callback) {
            if (!cpnyid || !transfertype || !department) {
                console.warn("Skipping loadFromWhsOptions: Missing cpnyid or transfertype");
                return;
            }

            // console.log(department,cpnyid,transfertype);

            $.ajax({
                url: "{{ route('getFromWhsOptionsTransfer') }}",
                type: "GET",
                data: { 
                        department: department,
                        cpnyid: cpnyid,
                        transfertype: transfertype,
                    },
                success: function(response) {
                    console.log("From WHS Response:", response);

                    if ($(`#from_whs_id_${rowIndex}`).length) {
                        $(`#from_whs_id_${rowIndex}`).val(response.whs_id);
                        console.log("From WHS set:", response.whs_id);

                        // **Panggil callback setelah warehouseId diperbarui**
                        if (typeof callback === "function") {
                            callback(response.whs_id);
                        }
                    } else {
                        console.warn("Element #from_whs_id_" + rowIndex + " not found");
                    }
                },
                error: function() {
                    alert("Failed to load warehouse data. Please try again.");
                }
            });
        }


        // Add dynamic rows for the transfer details
        $('#addRow').click(function() {
            i++;
            
            $('#dynamicTable').append('<tr id="row' + i + '"><td><input type="text" name="addmore[' + i + '][from_whs_id]" class="form-control" id="from_whs_id_' + i + '" readonly /></td><td><div class="input-group"><input type="hidden" name="addmore[' + i + '][product_id]" id="product_id_' + i + '" /><input type="text" name="addmore[' + i + '][product_name]" placeholder="Enter Product Name" class="form-control" id="product_name_' + i + '" readonly /><span class="input-group-btn"><button type="button" class="btn btn-primary open-product-modal" data-row-index="' + i + '" data-toggle="modal" data-target="#productModal_' + i + '"><i class="fa fa-search"></i></button></span></div></td><td><input type="text" name="addmore[' + i + '][qty_available]" placeholder="Enter Stock" class="form-control" id="qty_available_' + i + '" readonly /></td><td><input type="date" name="addmore[' + i + '][expired_date]" class="form-control" id="expired_date_' + i + '" readonly /></td><td><input type="text" name="addmore[' + i + '][qty_transfer]" placeholder="Enter Qty" value="0" class="form-control" id="qty_transfer_' + i + '"  /></td><td><select class="form-control select2" name="addmore[' + i + '][to_whs_id]" style="width: 100%;"><option value="">Select Source WHS</option></select></td><td><button type="button" name="remove" id="' + i + '" class="btn btn-danger btn_remove">Del</button></td></tr>');
                
            $('body').append(`
                <div class="modal fade" id="productModal_${i}" tabindex="-1" role="dialog" aria-labelledby="productModalLabel_${i}" aria-hidden="true">
                    <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Select Product</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <table id="example_${i}" class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>Product ID</th>
                                            <th>Product Name</th>
                                            <th>Stock</th>
                                            <th>Qty Transfer</th>
                                            <th>Expired Date</th>
                                            <th>WHS ID</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            `);

            $('.select2').select2();

            // Load data ke modal untuk baris baru
            var transfertype = $('#transfertype').val();
            var cpnyid = $('select[name="cpnyid"]').val();
            
            // Ambil refTransfer dari row pertama agar konsisten 
            var refTtransfer = $('#ref_transfer_id').val();
            if (i > 0) {
                // Pastikan refTtransfer sama dengan row pertama
                refTtransfer = $('#ref_transfer_id').val();
            }

                loadFromWhsOptions(department, cpnyid, transfertype, i, function(warehouseId) {
                loadProducts(cpnyid, transfertype, warehouseId,refTtransfer, i);
                loadToWhsOptions(cpnyid, transfertype,warehouseId,i);
            });
        });

        // Handle product selection in modal
        $(document).on('click', '.select-product', function() {
            var productId = $(this).data('product-id');
            var productName = $(this).data('product-name');
            var qty = $(this).data('qty');
            var expiredDate = $(this).data('expired-date');
            var whsId = $(this).data('whs-id');
            var rowIndex = $(this).data('row-index');

            $(`#product_id_${rowIndex}`).val(productId);
            $(`#product_name_${rowIndex}`).val(productName);
            $(`#qty_available_${rowIndex}`).val(qty);
            $(`#expired_date_${rowIndex}`).val(expiredDate);
            $(`#from_whs_id_${rowIndex}`).val(whsId);
        });

        // Remove row dynamically
        $(document).on('click', '.btn_remove', function() {
            var button_id = $(this).attr("id");
            $(`#row${button_id}`).remove();
        });

        function reloadDataTable(rowIndex, data) {
            $(`#example_${rowIndex}`).DataTable().clear().destroy(); // Hancurkan DataTable lama
            $(`#example_${rowIndex} tbody`).empty(); // Kosongkan isi tabel

            data.forEach(function (product) {
                $(`#example_${rowIndex} tbody`).append(`
                    <tr>
                        <td>${product.product_id}</td>
                        <td>${product.product_name}</td>
                        <td>${product.qty_available}</td>
                        <td>${product.qty_transfer}</td>
                        <td>${product.expired_date}</td>
                        <td>${product.whs_id}</td>
                        <td>
                            <button type="button" class="btn btn-primary select-product"
                                data-product-id="${product.product_id}"
                                data-product-name="${product.product_name}"
                                data-qty="${product.qty_available}"
                                data-expired-date="${product.expired_date}"
                                data-whs-id="${product.whs_id}"
                                data-row-index="${rowIndex}" 
                                data-dismiss="modal">Select</button>
                        </td>
                    </tr>
                `);
            });

            $(`#example_${rowIndex}`).DataTable({
                destroy: true,
                ordering: true
            });
        }

    });
    

</script>

@endsection
