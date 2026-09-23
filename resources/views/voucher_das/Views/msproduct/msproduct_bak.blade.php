@extends('layouts.template')

@section('header_scripts')

<meta name="csrf-token" content="{{ csrf_token() }}">

@endsection

@section('content')
<div class="content-wrapper" style="background-color: #051931;">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <ol class="breadcrumb">
            <li><a href="#" style="color: white"><i class="fa fa-dashboard"></i> Settings </a></li>
            <li class="active" style="color: white">Master Product</li>
        </ol>
    </section>
    {{-- <div class="box-header" style="display: flex; justify-content: between; margin-left: 0.5rem;">
        <a href="{{ url('/mswhs') }}" style="margin-right: 10px; background-color: #006EF0; color: white;" class="btn mb-1">Warehouse</a>
        <div style="display: flex;">
            <a href="{{ url('/mswhsdept') }}" style="margin-right: 10px; background-color: #ffffff; color: black;" class="btn mb-1">Warehouse Dept</a>
            <a href="{{ url('/msproduct') }}" style="margin-right: 10px; background-color: #EA002F; color: white;" class="btn mb-1">Master Product</a>
        </div>
    </div> --}}

    <!-- Main content -->
    <section class="content" style="margin-top:2rem">
        <div class="box box-info" style=" border-top:none; border-radius:10px;"> 
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h1 style="color: Black; padding-left:2rem; font-size:2rem;">Master Product</h1>
                <div>
                <a class="btn" style="background-color:#006EF0;color:white;margin-right: 10px"  href="javascript:void(0)" id="createNewMsproduct"> New Product</a>
                </div>
            </div>      
            <div class="modal-footer" style="border-top:none; border-radius:10px;">                    
               
            </div>
            <div class="box">               
                <div class="box-body">
                    <table class="table table-bordered data-table">
                        <thead>
                            <tr>                                
                                <th>Doc No</th>   
                                <th>CpnyID</th>
                                <th>Name</th>     
                                <th>Expired Date</th>
                                <th>Qty Available</th>                                                                                 
                                <th>Status</th>   
                                <th>Action</th>                                                         
                            </tr>
                        </thead>
                        <tbody>
                        </tbody> 
                    </table>
                </div>                
            </div>

            <div class="modal fade" id="ajaxModel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="modelHeading"></h4>
                        </div>
                        <div class="modal-body">
                            {{-- <form id="inputForm" name="inputForm" class="form-horizontal"> --}}
                            <form id="inputForm" name="inputForm" class="form-horizontal" enctype="multipart/form-data">
                                <input type="hidden" name="key_id" id="key_id">
            
                                <!-- Row container to hold two columns -->
                                <div class="row">
                                    <!-- First Column -->
                                    <div class="col-md-6">
                                        <div class="form-group">                                            
                                            <label for="product_source_company" class="col-sm-3 control-label text-right">Nama PT</label>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control" id="product_source_company" name="product_source_company" >
                                            </div>
                                        </div>
            
                                        <div class="form-group">
                                            <label for="product_source_tenant" class="col-sm-3 control-label text-right">Nama Tenant</label>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control" id="product_source_tenant" name="product_source_tenant" >
                                            </div>
                                        </div>
            
                                        <div class="form-group">
                                            <label for="cpnyid" class="col-sm-3 control-label text-right">Company</label>
                                            <div class="col-sm-9">
                                                <select class="form-control select2" id="cpnyidx" name="cpnyid" style="width: 100%;" >
                                                    <option selected="selected"></option>
                                                    @foreach($company as $p)
                                                        <option value="{{ $p->cpnyid }}">{{ $p->cpnyid }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
            
                                        <div class="form-group">
                                            <label for="product_name" class="col-sm-3 control-label text-right">Product Name</label>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control" id="product_name" name="product_name" >
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-sm-3 control-label text-right">Check Expired Date</label>
                                            <div class="col-sm-9">
                                                <input type="checkbox" id="product_check_exp" name="product_check_exp" value="1">
                                            </div>
                                        </div>
                                    </div>                                    
            
                                    <!-- Second Column -->
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="product_type" class="col-sm-3 control-label text-right">Product Type</label>
                                            <div class="col-sm-9">
                                                <select class="form-control select2" id="product_typex" name="product_type" style="width: 100%;" >
                                                    <option selected="selected"></option>
                                                    <option value="V">Voucher</option>
                                                    <option value="P">Product</option>
                                                </select>
                                            </div>
                                        </div>
            
                                        <div class="form-group">
                                            <label for="statusx" class="col-sm-3 control-label text-right">Category</label>
                                            <div class="col-sm-9">
                                                <select class="form-control select2" id="categoryx" name="product_category" style="width: 100%;" >
                                                    <option selected="selected"></option>
                                                    @foreach($category as $p)
                                                        <option value="{{ $p->categoryname }}">{{ $p->categoryname }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
            
                                        <div class="form-group">
                                            <label for="product_value" class="col-sm-3 control-label text-right">Value</label>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control" id="product_value" name="product_value" value="0" >
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="product_remark" class="col-sm-3 control-label text-right">Remarks</label>
                                            <div class="col-sm-9">
                                                <textarea class="form-control" rows="3" id="product_remark" name="product_remark" placeholder="Enter Remarks ..." ></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <hr>
                                <!-- Dynamic Table for Multiple Inputs -->
                                <h5 class="modal-title">Info Details</h5>
                                <table class="table table-bordered" id="dynamicTable">
                                    <thead>
                                        <tr>
                                            <th style="width: 15%;">Qty</th>
                                            <th style="width: 15%;">UOM</th>
                                            <th style="width: 20%;">Expired Date</th>
                                            <th style="width: 30%;">Source WHS</th>
                                            <th style="width: 10%;">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><input type="text" name="addmore[0][qty]" placeholder="Enter Qty" class="form-control" /></td>
                                            <td><input type="text" name="addmore[0][uom]" placeholder="Enter UOM" class="form-control" /></td>
                                            <td><input type="date" name="addmore[0][expired_date]" class="form-control" /></td>
                                            <td>
                                                <select class="form-control select2" id="addmore[0][source_whs]" name="addmore[0][source_whs]" style="width: 100%;" >
                                                    <option selected="selected"></option>
                                                    @foreach($mswhs as $p)
                                                        <option value="{{ $p->whs_id }}">{{ $p->whs_id }}</option>
                                                    @endforeach
                                                </select>                                               
                                            <td><button type="button" name="add" id="addRow" class="btn btn-success">Add</button></td>
                                        </tr>
                                    </tbody>
                                </table>
                                <hr>
                                                             
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
                                
                                <div class="modal-footer">
                                    <button type="button" class="btn pull-left" style="background-color:#EA002F;color:white" data-dismiss="modal">Close</button>
                                    <button type="submit" class="btn" style="background-color:#006EF0;color:white" id="saveBUTTON" value="create">Save</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            
              
        </div>
    </section>
</div>
@endsection

@section('footer_scripts')


<script type="text/javascript"> 
    var r={
    'special':/[\W]/g,
    'quotes':/['\''&'\"']/g,
    'notnumbers':/[^\d]/g,
    'notletters':/[A-Za-z]/g,
    'numbercomma':/[^\d.]/g,
    }
    
    function valid(o,w){
    o.value = o.value.replace(r[w],'');
    }
</script>

<script type="text/javascript">
    $(function () {   
        $.ajaxSetup({
          headers: {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          }
        });   
      var table = $('.data-table').DataTable({
        "order": [[0, "desc"]],
          processing: true,
          serverSide: true,
          ajax: {
            url: "{{ route('msproduct.msproduct') }}",             
         
          },
          columns: [            
              {data: 'product_id', name: 'product_id'}, 
              {data: 'cpnyid', name: 'cpnyid'}, 
              {data: 'product_name', name: 'product_name'},   
              {data: 'expired_date', name: 'expired_date'},
              {data: 'qty_available', name: 'qty_available'},                        
              {data: 'status', name: 'status'},  
              {data: 'action', name: 'action'},           
          ],
               
        dom:'lBfrtip',
          buttons: ['excel', 'csv', 'pdf', 'copy'],
          lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
          responsive: true,        
        });
       
        $('#createNewMsproduct').click(function () {
            $('#saveBUTTON').html("Save Product");
            $('#key_id').val('');
            $('#inputForm').trigger("reset");
            $('#modelHeading').html("Create New Product");
            $('#ajaxModel').modal('show');
            $('#cpnyidx').val(null).trigger('change');    
            $('#product_typex').val(null).trigger('change');                     
            $('#categoryx').val(null).trigger('change');
        });        
        
        $('body').on('click', '.editMsproduct', function () {
        var key_id = $(this).data('id');
        $.get("{{ route('msproduct.msproduct') }}" +'/' + key_id +'/edit', function (data) {
            $('#modelHeading').html("Edit Product");
            $('#saveBUTTON').html("Updated Product");
            $('#ajaxModel').modal('show');

            $('#key_id').val(data.msproduct.id);
            $('#product_source_company').val(data.msproduct.product_source_company);
            $('#product_source_tenant').val(data.msproduct.product_source_tenant);                  
            $('#cpnyidx').val(data.msproduct.cpnyid).trigger('change');    
            $('#product_name').val(data.msproduct.product_name); 
            $('#product_check_exp').val(data.msproduct.product_check_exp);       
            $('#product_typex').val(data.msproduct.product_type).trigger('change');
            $('#categoryx').val(data.msproduct.product_category).trigger('change');
            $('#product_value').val(data.msproduct.product_value); 
            $('#product_remark').val(data.msproduct.product_remark);

            // Bersihkan dynamicTable sebelum mengisi ulang
             $('#dynamicTable tbody').empty();

            // Isi detail Info Details ke dynamicTable
            $.each(data.msproductdetail, function(index, detail) {
                var newRow = `
                    <tr id="row${index}">
                        <td><input type="text" name="addmore[${index}][qty]" value="${detail.qty_available}" class="form-control" /></td>
                        <td><input type="text" name="addmore[${index}][uom]" value="${detail.uom}" class="form-control" /></td>
                        <td><input type="date" name="addmore[${index}][expired_date]" value="${detail.expired_date}" class="form-control" /></td>
                        <td>
                            <select class="form-control select2" name="addmore[${index}][source_whs]" style="width: 100%;">
                                <option value="${detail.whs_id}" selected>${detail.whs_id}</option>
                                @foreach($mswhs as $p)
                                    <option value="{{ $p->whs_id }}">{{ $p->whs_id }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td><button type="button" name="remove" id="${index}" class="btn btn-danger btn_remove">Del</button></td>
                    </tr>
                `;

                $('#dynamicTable tbody').append(newRow);
                $('.select2').select2(); // Inisialisasi kembali select2 untuk WHS setelah menambahkan baris
            });

            // Bersihkan dynamicTable2 sebelum mengisi ulang
            $('#dynamicTable2 tbody').empty();

            // Isi attachment ke dynamicTable2
            $.each(data.attachments, function(index, attachment) {
                var newRow = `
                    <tr id="attachmentRow${index}">
                        <td><a href="{{ url('/attachment') }}/${attachment.created_at}/${attachment.attahfile}" target="_blank">${attachment.name}</a></td>
                        <td><button type="button" name="remove" id="attachment${index}" class="btn btn-danger btn_remove_attachment">Del</button></td>
                    </tr>
                `;
                $('#dynamicTable2 tbody').append(newRow);
            });

            $(document).on('click', '.btn_remove_attachment', function() {
                var button_id = $(this).attr("id");
                $('#attachmentRow' + button_id + '').remove();

                // Anda juga bisa melakukan operasi lain seperti mengirim request untuk menghapus attachment dari database
            });

        })
        });        
        
        $('#saveBUTTON').click(function(e) {
            e.preventDefault();

            // Memeriksa validasi HTML5 browser sebelum mengirim form
            var form = $('#inputForm')[0];
            
            if (validateForm() && validateDynamicTable()) {
                $(this).html('Sending..');

                var formData = new FormData(form);

                $.ajax({
                    data: formData,
                    contentType: false,   
                    processData: false,
                    url: "{{ route('msproduct.save_product') }}",
                    type: "POST",
                    dataType: 'json',
                    success: function(data) {
                        $('#inputForm').trigger("reset");
                        $('#ajaxModel').modal('hide');
                        table.draw();
                    },
                    error: function(data) {
                        console.log('Error:', data);
                        $('#saveBUTTON').html('Save Changes');
                    }
                });
            } else {
                // Trigger form validation
                console.log("Form tidak valid.");
            }
        });


        $('#cpnyidx').select2({
            width: '100%',
            placeholder: 'Select a Company'
        });       
        $('#category').select2({
            width: '100%',
            placeholder: 'Select a Category'
        });
                
    });
    
</script>
<script type="text/javascript">
    $(document).ready(function() {
        var i = 0;
        
        // Add new row
        $('#addRow').click(function() {
            i++;
            $('#dynamicTable').append('<tr id="row' + i + '"><td><input type="text" name="addmore[' + i + '][qty]" placeholder="Enter Qty" class="form-control" /></td><td><input type="text" name="addmore[' + i + '][uom]" placeholder="Enter UOM" class="form-control" /></td><td><input type="date" name="addmore[' + i + '][expired_date]" class="form-control" /></td><td><select class="form-control select2" name="addmore[' + i + '][source_whs]" style="width: 100%;" ><option selected="selected"></option>@foreach($mswhs as $p)<option value="{{ $p->whs_id }}">{{ $p->whs_id }}</option>@endforeach</select></td><td><button type="button" name="remove" id="' + i + '" class="btn btn-danger btn_remove">Del</button></td></tr>');
            $('.select2').select2();
        });
        
        // Remove row
        $(document).on('click', '.btn_remove', function() {
            var button_id = $(this).attr("id");
            $('#row' + button_id + '').remove();
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
<script>
    // Fungsi validasi
    function validateForm() {
        let isValid = true;

        // Reset pesan error
        $('.error-message').remove();

        // Validasi Nama PT
        if ($('#product_source_company').val().trim() === '') {
            $('#product_source_company').after('<span class="error-message" style="color: red;">Nama PT is required</span>');
            isValid = false;
        }

        // Validasi Nama Tenant
        if ($('#product_source_tenant').val().trim() === '') {
            $('#product_source_tenant').after('<span class="error-message" style="color: red;">Nama Tenant is required</span>');
            isValid = false;
        }

        // Validasi Company (Select2)
        if ($('#cpnyidx').val() === null || $('#cpnyidx').val() === '') {
            $('#cpnyidx').after('<span class="error-message" style="color: red;">Company is required</span>');
            isValid = false;
        }

        // Validasi Product Name
        if ($('#product_name').val().trim() === '') {
            $('#product_name').after('<span class="error-message" style="color: red;">Product Name is required</span>');
            isValid = false;
        }

        // Validasi Product Type (Select2)
        if ($('#product_type').val() === null || $('#product_type').val() === '') {
            $('#product_type').after('<span class="error-message" style="color: red;">Product Type is required</span>');
            isValid = false;
        }

        // Validasi Category (Select2)
        if ($('#categoryx').val() === null || $('#categoryx').val() === '') {
            $('#categoryx').after('<span class="error-message" style="color: red;">Category is required</span>');
            isValid = false;
        }

        // Validasi Product Value
        if ($('#product_value').val().trim() === '') {
            $('#product_value').after('<span class="error-message" style="color: red;">Product Value is required</span>');
            isValid = false;
        }

        return isValid;
    }

    function validateDynamicTable() {
        var isValid = true;

        // Loop through each row in the dynamic table
        $('#dynamicTable tbody tr').each(function() {
            var qty = $(this).find('input[name^="addmore"]').filter('[name*="[qty]"]').val();
            var uom = $(this).find('input[name^="addmore"]').filter('[name*="[uom]"]').val();
            var expiredDate = $(this).find('input[name^="addmore"]').filter('[name*="[expired_date]"]').val();
            var sourceWhs = $(this).find('select[name^="addmore"]').filter('[name*="[source_whs]"]').val();

            // Check if any field is empty
            if (!qty || !uom || !expiredDate || !sourceWhs) {
                isValid = false;
                alert("Info Detail Empty !");
                return false; // Break the loop
            }
        });

        return isValid;
    }


</script>
    

@endsection

