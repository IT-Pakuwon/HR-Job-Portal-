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
        <h1 style="color: white">Create Usage</h1>
        <ol class="breadcrumb" style="background: transparent;">
            <li><a href="#" style="color: white;font-size:13px"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active" style="color: white;font-size:13px">Create Usage</li>
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
                            <label for="inputEmail3" class="col-sm-1 control-label" ><span style="color:red;font-weight:bold"> *</span>Type</label>                            
                            <div class="col-sm-3">
                                <select class="form-control select2" id="requesttype" name="requesttype" style="width: 100%;" >
                                    <option selected="selected"></option>
                                    <option value="Usage">Usage</option>
                                    <option value="Return">Return</option>
                                    {{-- <option value="Adjusment">Adjusment</option> --}}
                                </select>                               
                            </div>     
                              
                        </div>                        
                    </div>      
                    <div class="box-body doc-date-section">                        
                        <div class="col-md-12">  
                            <label for="inputEmail3" class="col-sm-1 control-label">Doc Date <span style="color:red;font-weight:bold"> *</span></label>
                            <div class="col-sm-2">
                                <div class="input-group date">
                                    <div class="input-group-addon">
                                        <i class="fa fa-calendar"></i>
                                    </div>
                                    <input type="text" class="form-control pull-right" id="datepicker" name="doc_date" value="{{ date('m/d/Y') }}">
                                
                                </div> 
                            </div>                            
                        </div>
                    </div>
                    <div class="box-body">                        
                        <div class="col-md-12">                              
                            <label for="inputEmail3" class="col-sm-1 control-label">Remark</label>
                            <div class="col-sm-4">
                                <textarea type="text" rows="3" class="form-control" name="request_remark"  placeholder="Enter Remarks ..."></textarea>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="box-header with-border">                        
                        <div class="box-tools pull-right">
                            <a href="javascript:void(0)" id="openAddModal" class="btn mb-1 btn-success">ADD</a>
                            <a href="javascript:void(0)" id="openAddModalreturn" class="btn mb-1 btn-success">LOAD</a>
                        </div>
                        <h5 class="modal-title">Usage Details</h5>
                    </div>
                    <div class="box-body">      
                        <table class="table table-bordered" id="productTemp">
                            <thead>
                                <tr>                                  
                                 
                                </tr>
                            </thead>                   
                            <tbody id="productTempTableBody">
                                <!-- Data will be dynamically inserted here -->
                            </tbody>
                        </table>
                    </div>
                                      
                   
                    <div class="modal fade" id="ajaxModel" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h4 class="modal-title" id="modelHeading">Add Product Details</h4>
                                </div>
                                <div class="modal-body">
                                    <form id="inputForm" name="inputForm" class="form-horizontal" enctype="multipart/form-data">
                                    
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <input type="hidden" name="refid" value="{{ $refid }}">
                                        <div class="box-body">
                                            <div class="col-md-12">                            
                                                <label for="inputEmail3" class="col-sm-1 control-label" >Product Name</label>                            
                                                <div class="col-sm-7">
                                                    <select class="form-control select2" name="product_id" id="product_id" style="width: 100%;" required>                                    
                                                       
                                                    </select>                               
                                                </div>     
                                                
                                                <label for="inputEmail3" class="col-sm-1 control-label" >Qty</label>                            
                                                <div class="col-sm-2">
                                                    <input type="text" name="qty_request" id="qty_request" class="form-control">                             
                                                </div>                                                       
                                            </div>                        
                                        </div>    
                                        {{-- table detail --}}
                                        <table class="table table-bordered mt-3" id="productDetailTable">
                                            <thead>
                                                <tr>                                                    
                                                    <th>Product ID</th>
                                                    <th>Product Name</th>
                                                    <th>Quantity Available</th>
                                                    <th>Expired Date</th>
                                                </tr>
                                            </thead>
                                            <tbody id="productDetailTableBody">
                                                <!-- Data will be dynamically inserted here -->
                                            </tbody>
                                        </table>
                    
                                        <div class="modal-footer">
                                            <button type="button" class="btn pull-left" style="background-color:#EA002F;color:white" data-dismiss="modal">Close</button>
                                            <button type="submit" class="btn" style="background-color:#006EF0;color:white" id="saveTEMP" value="create">ADD</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal fade" id="ajaxModelreturn" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h4 class="modal-title" id="modelHeading">Load Product</h4>
                                </div>
                                <div class="modal-body">                                    
                                    <form id="inputFormReturn" name="inputFormReturn" class="form-horizontal" enctype="multipart/form-data">

                                    
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <input type="hidden" name="refid" value="{{ $refid }}">
                                        <div class="box-body">
                                            <div class="col-md-12">                            
                                                <label for="inputEmail3" class="col-sm-1 control-label" >Product Name</label>                            
                                                <div class="col-sm-7">
                                                    <select class="form-control select2" name="request_id" id="request_id" style="width: 100%;" required>                                    
                                                       
                                                    </select>                               
                                                </div>                                                     
                                             
                                            </div>                        
                                        </div>    
                                        
                                        <table class="table table-bordered mt-3" id="productDetailTable2">
                                            <thead>
                                                <tr>                                                    
                                                    <th>Product ID</th>
                                                    <th>Product Name</th>
                                                    <th>Quantity</th>
                                                    <th>Expired Date</th>
                                                </tr>
                                            </thead>
                                            <tbody id="productDetailTableBody2">                                              
                                            </tbody>
                                        </table>
                    
                                        <div class="modal-footer">
                                            <button type="button" class="btn pull-left" style="background-color:#EA002F;color:white" data-dismiss="modal">Close</button>
                                            <button type="submit" class="btn" style="background-color:#006EF0;color:white" id="saveTEMPReturn" value="create">LOAD</button>
                                            {{-- <button type="submit" class="btn" style="background-color:#006EF0;color:white" id="saveTEMP" value="create">LOAD</button> --}}
                                        </div>
                                    </form>
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
                    <a href="{{ url('/vplrequest_waiting') }}" style="margin-right: 10px;background-color:#EA002F; color:white" class="btn mb-1">Cancel</a>
                   
            </form>
        </div>
    </section>
    <!-- /.content -->
</div>
@endsection
@section('footer_scripts')
<script type="text/javascript"> 
   $(document).ready(function () {
        function toggleDocDate() {
            var selectedDept = $('select[name="department"]').val();
            var selectedType = $('select[name="requesttype"]').val();

            if (selectedDept === "CUSTOMERSERVICE" && selectedType === "Usage") {
                $('.doc-date-section').show();
            } else {
                $('.doc-date-section').hide();
            }
        }

        // Panggil fungsi saat halaman pertama kali dimuat
        toggleDocDate();

        // Panggil fungsi saat dropdown department atau requesttype berubah
        $('select[name="department"], select[name="requesttype"]').on('change', function () {
            toggleDocDate();
        });
    });

</script>

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
    $(document).ready(function () {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $('#saveBUTTON').click(function (e) {
            e.preventDefault();

            // Reset pesan error sebelumnya
            $('.error-message').remove();
            $('#productTempTableBody tr').removeClass('row-error'); // Reset warna merah pada row

            var docDate = $('#datepicker').val(); // Ambil nilai doc_date dari input
            var selectedDate = new Date(docDate);
            var today = new Date();
            today.setHours(0, 0, 0, 0); // Set jam ke 00:00:00 agar hanya membandingkan tanggal
            
            var sevenDaysAgo = new Date();
            sevenDaysAgo.setDate(today.getDate() - 7); // Set H-7
            console.log('selectedDate: ',selectedDate,' sevenDaysAgo :',sevenDaysAgo, ' today :',today)
            if (selectedDate < sevenDaysAgo || selectedDate > today) {
                alert("Doc Date harus dalam rentang H-7 hingga hari ini!");
                return false; // Cegah pengiriman form
            }

            // Lakukan validasi
            let isValid = validateForm();

            if (!isValid) {
                return false;
            }

            validateProductAvailability().then((isAvailable) => {
                if (!isAvailable) {
                    toastr.error('Stock sudah direserved!');
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
                
                var requestId = $('select[name="request_id"]').val(); // Ambil nilai request_id dari dropdown
                if (requestId) {
                    formData.append('request_id', requestId);
                }

                $.ajax({
                    type: 'POST',
                    url: "{{ route('savevplrequest') }}", // Ubah dengan route yang sesuai
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function (response) {
                        toastr.success('Usage saved successfully!');

                        // setTimeout(function () {
                            window.location.href = "{{ route('vplrequest_waiting.vplrequest_waiting') }}";
                        // }, 1000);
                    },
                    error: function (xhr) {
                        if (xhr.status === 422) {
                            var errorMessage = xhr.responseJSON.error;
                            alert(errorMessage);
                        } else {
                            alert('Error occurred while saving. Please check your inputs.');
                        }
                    },
                    complete: function () {
                        button.prop('disabled', false);
                        button.html(originalText);
                    }
                });
            }).catch((error) => {
                console.error('Validation error:', error);
            });
        });

        // Validasi form utama
        function validateForm() {
            let isValid = true;
            var requesttype = $('select[name="requesttype"]').val();

            isValid &= validateSelectField('cpnyid', 'Company is required');
            isValid &= validateSelectField('department', 'Department is required');
            isValid &= validateSelectField('requesttype', 'Type is required');
            console.log('requesttype',requesttype);
            if (requesttype === 'Usage') {
                $('#productTempTableBody tr').each(function () {
                    const row = $(this);
                    const rowIndex = row.index() + 1;
                    
                    const purposeIdField = row.find('[name^="purpose_id"]');
                    // const purposeRemarkField = row.find('[name^="purpose_remark"]');
                    // console.log('purposeRemarkField',purposeRemarkField);
                    if (purposeIdField.val() === '' || purposeIdField.val() == null) {
                        isValid = false;
                        if (row.find('.error-purpose_id').length === 0) {
                            purposeIdField.after('<span class="error-message error-purpose_id" style="color: red;">Purpose ID is required (Row ' + rowIndex + ')</span>');
                        }
                    } else {
                        row.find('.error-purpose_id').remove();
                    }

                });
            }else{
                $('#productTempTableBody tr').each(function () {
                    const row = $(this);
                    const rowIndex = row.index() + 1;

                    const qtyUsageField = parseFloat(row.find('td:nth-child(3)').text().trim()) || 0; // Qty Usage
                    const qtyReturnField = parseFloat(row.find('td:nth-child(4) input').val().trim()) || 0; // Qty Return input field
                    console.log('usage :',qtyUsageField);
                    console.log('return :',qtyReturnField);
                    // Reset error sebelumnya
                    row.removeClass('row-error');
                    row.find('.error-message').remove();

                    // Validasi jika Qty Return kosong atau nol
                    if (qtyReturnField <= 0) {
                        isValid = false;
                        row.addClass('row-error'); // Tambahkan kelas untuk menandai baris sebagai error
                        row.find('td:nth-child(4)').append('<span class="error-message" style="color: red;">Qty Return is required and must be greater than 0 (Row ' + rowIndex + ')</span>');
                    }

                    // Validasi jika Qty Return lebih besar dari Qty Usage
                    if (qtyReturnField > qtyUsageField) {
                        isValid = false;
                        row.addClass('row-error');
                        row.find('td:nth-child(4)').append('<span class="error-message" style="color: red;">Qty Return cannot exceed Qty Usage (Row ' + rowIndex + ')</span>');
                    }
                });
            }

            return isValid;
        }

       
        async function validateProductAvailability() {
            let isAvailable = true;

            // Ambil nilai requesttype dari dropdown
            const requesttype = $('select[name="requesttype"]').val();
            const cpnyid = $('select[name="cpnyid"]').val()
            const department = $('select[name="department"]').val()

            if (requesttype === 'Usage') {
                // Validasi untuk 'Usage'
                const rows = $('#productTempTableBody tr');
                for (let i = 0; i < rows.length; i++) {
                    const row = $(rows[i]);
                    const productId = row.find('td:nth-child(1)').text().trim(); // Product ID
                    // const whsId = 'WHS_LOYALTY'; // Tambahkan sesuai kebutuhan
                    const expiredDate = row.find('td:nth-child(4)').text().trim(); // Expired Date
                    const uniqueParam = `?timestamp=${new Date().getTime()}`;

                    try {
                        const response = await $.ajax({
                            url: `/validate-stock/${productId}${uniqueParam}`,
                            type: 'GET',
                            dataType: 'json',
                            data: {
                                cpnyid: cpnyid,
                                department: department,
                                expired_date: expiredDate,
                            },
                        });

                        // Log untuk memverifikasi respons
                        console.log(`Product ID: ${productId}`);
                        console.log(`qty_available: ${response.qty_available}`);
                        console.log(`qty_reserved: ${response.qty_reserved}`);

                        // Jika stok tidak mencukupi
                        if (response.qty_available - response.qty_reserved <= 0) {
                            isAvailable = false;
                            row.addClass('row-error'); // Menambahkan kelas untuk memberi warna merah
                        } else {
                            row.removeClass('row-error'); // Hapus kelas jika stok mencukupi
                        }
                    } catch (error) {
                        console.error(`Error validating stock for product ${productId}:`, error);
                        isAvailable = false; // Set isAvailable to false if error occurs
                    }
                }
            } else if (requesttype === 'Return') {
                const rows = $('#productTempTableBody tr');
                    for (let i = 0; i < rows.length; i++) {
                        const row = $(rows[i]);
                        const productId = row.find('td:nth-child(1)').text().trim(); // Product ID
                        const qtyReturn = parseFloat(row.find('td:nth-child(4) input').val().trim()) || 0; // Qty Return input field
                        const expiredDate = row.find('td:nth-child(5)').text().trim(); // Assumes expired_date is in the 5th column
                        const uniqueParam = `?timestamp=${new Date().getTime()}`;
                        
                        console.log('product oi:', productId);
                        console.log('expired_date:', expiredDate);

                        try {
                            const response = await $.ajax({
                                url: `/validate-return/${productId}${uniqueParam}`,
                                type: 'GET',
                                data: {
                                    qty_return: qtyReturn,
                                    expired_date: expiredDate, // Send expired_date as a parameter
                                },
                                dataType: 'json',
                            });

                            // Log untuk memverifikasi respons
                            console.log(`Product ID: ${productId}`);
                            console.log(`qty_usage: ${response.qty_usage}`);
                            console.log(`qty_return: ${response.qty_return}`);

                            // Validasi jika qty_return melebihi qty_usage
                            if (qtyReturn > response.qty_usage) {
                                isAvailable = false;
                                row.addClass('row-error'); // Menandai baris sebagai error
                                row.find('td:nth-child(4)').append(
                                    '<span class="error-message" style="color: red;">Qty Return cannot exceed Qty Usage</span>'
                                );
                            } else {
                                row.removeClass('row-error'); // Hapus tanda error
                            }
                        } catch (error) {
                            console.error(`Error validating return for product ${productId}:`, error);
                            isAvailable = false; // Set isAvailable to false if error occurs
                        }
                    }

            }

            return isAvailable;
        }




        // Fungsi validasi dropdown
        function validateSelectField(fieldName, errorMessage) {
            let field = $('select[name="' + fieldName + '"]');
            let errorSpan = field.next('.error-message');
            
            if (field.val() === '') {
                if (errorSpan.length === 0) {
                    field.after('<span class="error-message" style="color: red;">' + errorMessage + '</span>');
                }
                return false;
            } else {
                errorSpan.remove();
            }
            return true;
        }

        // Tambahkan gaya untuk baris error
        $("<style>")
            .prop("type", "text/css")
            .html(`
                .row-error {
                    background-color: #f8d7da !important;
                    color: #721c24;
                }
            `)
            .appendTo("head");
    });
</script>

<script>
   $(document).ready(function () {
    var cpnyid = $('select[name="cpnyid"]').val()
    var department = $('select[name="department"]').val()
    console.log(cpnyid,' ',department);
        function loadProducts(cpnyid,department) {
            if (cpnyid) {
                $.ajax({
                    url: '/products/' + cpnyid,
                    type: 'GET',
                    data: { department: department },
                    dataType: 'json',
                    success: function (data) {
                        $('select[name="product_id"]').empty();
                        $('select[name="product_id"]').append('<option value="">Select Product</option>');
                        $.each(data, function (key, product) {
                            $('select[name="product_id"]').append('<option value="' + product.product_id + '">' + product.product_name + '</option>');
                        });
                    },
                    error: function (xhr, status, error) {
                        console.error('Error fetching products:', error);
                    }
                });
            } else {
                $('select[name="product_id"]').empty().append('<option value="">Select Product</option>');
            }
        }

        function loadProductsreturn(cpnyid,department) {
            if (cpnyid) {
                $.ajax({
                    url: '/productsreturn/' + cpnyid,
                    type: 'GET',
                    data: { department: department },
                    dataType: 'json',
                    success: function (data) {
                        $('select[name="request_id"]').empty();
                        $('select[name="request_id"]').append('<option value="">Select Usage</option>');
                        $.each(data, function (key, vplrequest) {
                            $('select[name="request_id"]').append('<option value="' + vplrequest.request_id + '">' + vplrequest.request_id + ' > '+ vplrequest.request_remark + '</option>');
                        });
                    },
                    error: function (xhr, status, error) {
                        console.error('Error fetching products:', error);
                    }
                });
            } else {
                $('select[name="request_id"]').empty().append('<option value="">Select Usage</option>');
            }
        }
    
        function loadExistingEntries(refid) {
            var selectedRequestType = $('select[name="requesttype"]').val();
            $.ajax({
                url: '/existing-entries/' + refid,
                type: 'GET',
                dataType: 'json',
                data: { selectedRequestType: selectedRequestType }, 
                success: function (data) {
                    console.log(data);
                    $('#productTempTableBody').empty();
                    $.each(data, function (index, detail) {                   
                        var newRow = `
                            <tr id="row-${detail.id}">                           
                                <td>${detail.product_id}</td>
                                <td>${detail.product_name || 'N/A'}</td>
                                <td>${detail.qty_request}</td>                               
                                <td>${detail.expired_date}</td>
                                 <td>
                                    <select name="purpose_id[${detail.id}]" class="form-control purposeDropdown">
                                        <option value="Redeem PG Card">Redeem PG Card</option>
                                        <option value="Promotion">Promotion</option>
                                        <option value="Entertaiment">Entertaiment</option>
                                        <option value="Management">Management</option>
                                        <option value="Dijual">Dijual</option>
                                        <option value="Write Off">Write Off</option>

                                    </select>
                                </td>
                                <td>
                                    <input type="text" name="purpose_remark[${detail.id}]" class="form-control purposeRemark" placeholder="Enter remark">
                                </td>
                                <td><button class="btn btn-danger btn-sm deleteRow" data-id="${detail.id}">DEL</button></td>
                            </tr>`;
                        $('#productTempTableBody').append(newRow);
                    });
                },
                error: function (xhr, status, error) {
                    console.error('Error fetching existing entries:', error);
                }
            });
        }

        function loadExistingEntriesreturn(refid) {
            
            console.log("Memuat data untuk refid:", refid);

            // Kosongkan tabel sebelum memuat ulang data
            $('#productTempTableBody').empty();
            var selectedRequestType = $('select[name="requesttype"]').val();
            $.ajax({
                url: '/existing-entries/' + refid,
                type: 'GET',
                dataType: 'json',
                data: { selectedRequestType: selectedRequestType }, 
                success: function (data) {
                    console.log("Data baru diterima:", data);

                    // Tambahkan data baru ke tabel
                    $.each(data, function (index, detail) {
                        var newRow = `
                            <tr id="row-${detail.id}">                           
                                <td>${detail.product_id}</td>
                                <td>${detail.product_name || 'N/A'}</td>
                                <td>${detail.qty_request}</td>
                                <td><input type="text" class="form-control qty-return-input" name="qty_return[${detail.id}]" value="${detail.qty_return || ''}" placeholder="Enter Qty Return"></td>
                                <td>${detail.expired_date}</td>
                                <td>${detail.purpose_id}</td>
                                <td><button class="btn btn-danger btn-sm deleteRow" data-id="${detail.id}">DEL</button></td>
                            </tr>`;
                        $('#productTempTableBody').append(newRow);
                    });
                },
                error: function (xhr, status, error) {
                    console.error('Error fetching existing entries:', xhr.responseText || error);
                }
            });
        }



        $('#openAddModal').click(function () {
            var cpnyid = $('select[name="cpnyid"]').val();
            var department = $('select[name="department"]').val();
            var refid = $('input[name="refid"]').val();
            $('#productDetailTableBody').empty();
            loadProducts(cpnyid,department);
            loadExistingEntries(refid);       
            $('#ajaxModel').modal('show');
        });

        $('#openAddModalreturn').click(function () {
            var cpnyid = $('select[name="cpnyid"]').val();
            var department = $('select[name="department"]').val();
            var refid = $('input[name="refid"]').val();
            $('#productDetailTableBody2').empty();
            loadProductsreturn(cpnyid,department);
            loadExistingEntriesreturn(refid);       
            $('#ajaxModelreturn').modal('show');
        });

        $('select[name="cpnyid"]').on('change', function () {
            var cpnyid = $(this).val();
            var department = $('select[name="department"]').val()
            loadProducts(cpnyid,department);
        });
        $('select[name="department"]').on('change', function () {
            var cpnyid = $('select[name="cpnyid"]').val()
            var department = $(this).val();
            loadProducts(cpnyid,department);
        });

        function toggleButtons(requesttype) {
            if (requesttype === 'Usage') {
                $('#openAddModal').show(); // Tampilkan tombol ADD
                $('#openAddModalreturn').hide(); // Sembunyikan tombol LOAD
            } else if (requesttype === 'Return') {
                $('#openAddModal').hide(); // Sembunyikan tombol ADD
                $('#openAddModalreturn').show(); // Tampilkan tombol LOAD
            } else {
                // Jika requesttype tidak sesuai, sembunyikan kedua tombol
                $('#openAddModal').hide();
                $('#openAddModalreturn').hide();
            }

            const tableHeader = $('#productTemp thead tr');
            tableHeader.empty(); // Kosongkan header tabel

            if (requesttype === 'Usage') {
                tableHeader.append(`
                    <th style="width: 5%;"><span style="color:red;font-weight:bold"> *</span>Product ID</th>
                    <th style="width: 20%;"><span style="color:red;font-weight:bold"> *</span>Product Name</th>
                    <th style="width: 5%;"><span style="color:red;font-weight:bold"> *</span>Qty Usage</th>
                    <th style="width: 5%;"><span style="color:red;font-weight:bold"> *</span>Expired Date</th>
                    <th style="width: 8%;"><span style="color:red;font-weight:bold"> *</span>Purpose</th>
                    <th style="width: 15%;"><span style="color:red;font-weight:bold"> </span>Remarks</th>                    
                    <th style="width: 3%;">Action</th>
                `);
            } else {
                tableHeader.append(`
                    <th style="width: 5%;"><span style="color:red;font-weight:bold"> *</span>Product ID</th>
                    <th style="width: 30%;"><span style="color:red;font-weight:bold"> *</span>Product Name</th>
                    <th style="width: 8%;"><span style="color:red;font-weight:bold"> *</span>Qty Usage</th>
                    <th style="width: 8%;"><span style="color:red;font-weight:bold"> *</span>Qty Return</th>
                    <th style="width: 8%;"><span style="color:red;font-weight:bold"> *</span>Expired Date</th>
                    <th style="width: 8%;"><span style="color:red;font-weight:bold"> *</span>Purpose</th>
                    <th style="width: 5%;">Action</th>
                `);
            }
        }

        // Set kondisi awal berdasarkan nilai awal dari requesttype
        const initialRequestType = $('select[name="requesttype"]').val();
        toggleButtons(initialRequestType);

        // Event listener untuk perubahan nilai pada requesttype
        $('select[name="requesttype"]').on('change', function () {
            const requesttype = $(this).val();
            toggleButtons(requesttype);
        });
    

        $('select[name="product_id"]').on('change', function () {
            var product_id = $(this).val();
            var cpnyid = $('select[name="cpnyid"]').val()
            var department = $('select[name="department"]').val()
            if (cpnyid && product_id) {
                $.ajax({
                    url: '/product-details/' + cpnyid + '/' + product_id,
                    type: 'GET',
                    data: { department: department },
                    dataType: 'json',
                    success: function (data) {
                        // Clear the product details table body
                        $('#productDetailTableBody').empty();
                        
                        // Populate new rows based on the fetched data
                        $.each(data, function (index, detail) {
                            var newRow = `
                                <tr>
                                    <td>${detail.product_id}</td>
                                    <td>${detail.product_name}</td>
                                    <td>${detail.qty_available}</td>
                                    <td>${detail.expired_date}</td>
                                </tr>`;
                            $('#productDetailTableBody').append(newRow);
                        });
                    },
                    error: function (xhr, status, error) {
                        console.error('Error fetching product details:', error);
                    }
                });
            } else {
                // Clear the table if no product_id is selected
                $('#productDetailTableBody').empty();
            }
        });

        $('select[name="request_id"]').on('change', function () {
            var request_id = $(this).val();
            var cpnyid = $('select[name="cpnyid"]').val()
            var department = $('select[name="department"]').val()
            if (cpnyid && request_id) {
                $.ajax({
                    url: '/product-details-return/' + cpnyid + '/' + request_id,
                    type: 'GET',
                    data: { department: department },
                    dataType: 'json',
                    success: function (data) {
                        console.log(data);
                        // Clear the product details table body
                        $('#productDetailTableBody2').empty();
                        
                        // Populate new rows based on the fetched data
                        $.each(data, function (index, detail) {
                            var newRow = `
                                <tr>
                                    <td>${detail.product_id}</td>
                                    <td>${detail.product_name}</td>
                                    <td>${detail.qty_request}</td>                              
                                    <td>${detail.expired_date}</td>
                                </tr>`;
                            $('#productDetailTableBody2').append(newRow);
                        });
                    },
                    error: function (xhr, status, error) {
                        console.error('Error fetching product details:', error);
                    }
                });
            } else {
                // Clear the table if no product_id is selected
                $('#productDetailTableBody').empty();
            }
        });

        $(document).on('click', '.deleteRow', function () {        
            var rowId = $(this).data('id');       
            var row = $(this).closest('tr');

            if (!rowId) {
                console.error('Row ID is undefined');
                return;
            }

            $.ajax({
                    url: `/delete-entry/${rowId}`,
                    type: 'DELETE',
                    data: { _token: $('meta[name="csrf-token"]').attr('content') },
                    success: function () {
                        row.remove(); // Remove row from table
                    },
                    error: function (xhr, status, error) {
                        console.error('Error deleting entry:', xhr.responseText || error);
                    }
            });
        });

        $('#saveTEMP').click(function (e) {
            e.preventDefault();
            var selectedRequestType = $('select[name="requesttype"]').val();
            var formData = new FormData($('#inputForm')[0]);
            var refid = $('input[name="refid"]').val();

            formData.append('requesttype', selectedRequestType);

                $.ajax({
                    data: formData,
                    url: "{{ route('addvplrequest.save_addvplrequesttemp') }}",
                    type: "POST",
                    dataType: 'json',
                    contentType: false,
                    processData: false,
                    success: function (data) {
                        // $('#inputForm').trigger("reset");
                        $('#inputForm select[name="product_id"]').val('').trigger('change'); // Reset dropdown produk
                        $('#inputForm input[name="qty_request"]').val(''); // Reset input qty
                        $('#inputForm input[name="expired_date"]').val(''); // Reset input tanggal
                        $('select[name="requesttype"]').val(selectedRequestType).trigger('change');
                        $('#ajaxModel').modal('hide');
                        loadExistingEntries(refid);
                    
                    },
                    error: function (xhr, status, error) {
                        console.error('Error:', xhr.responseText || error);
                    }
                });
        });

        $('#saveTEMPReturn').click(function (e) {
            e.preventDefault();

            var formData = new FormData($('#inputFormReturn')[0]);
            var refid = $('input[name="refid"]').val();        
            var selectedRequestType = $('select[name="requesttype"]').val();

            if (!refid) {
                console.error("Refid tidak valid:", refid);
                return;
            }

            formData.append('requesttype', selectedRequestType);

            $.ajax({
                data: formData,
                url: "{{ route('addvplreturn.save_addvplreturn') }}", // Sesuaikan route Anda
                type: "POST",
                dataType: 'json',
                contentType: false,
                processData: false,
                success: function (data) {
                    console.log("Data berhasil disimpan:", data);

                    // $('#inputFormReturn').trigger("reset");
                    $('#inputFormReturn select[name="product_id"]').val('').trigger('change'); // Reset dropdown produk
                    $('#inputFormReturn input[name="qty_request"]').val(''); // Reset input qty
                    $('#inputFormReturn input[name="expired_date"]').val(''); // Reset input tanggal
                    $('#ajaxModelreturn').modal('hide'); // Tutup modal setelah berhasil
                    loadExistingEntriesreturn(refid); // Memuat data terbaru
                },
                error: function (xhr, status, error) {
                    console.error('Error saat menyimpan:', xhr.responseText || error);
                }
            });
        });


    
    });

    


</script>
    
   
    
    
@endsection
