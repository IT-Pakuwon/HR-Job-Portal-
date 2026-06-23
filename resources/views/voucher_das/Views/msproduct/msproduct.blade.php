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
            <li class="active" style="color: white">{{ $title }}</li>
        </ol>
    </section>  

    <!-- Main content -->
    <section class="content" style="margin-top:2rem">
        <div class="box box-info" style=" border-top:none; border-radius:10px;"> 
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h1 style="color: Black; padding-left:2rem; font-size:2rem;">{{ $title }}</h1>
                <div>
                <a class="btn" style="background-color:#006EF0;color:white;margin-right: 10px"  href="javascript:void(0)" id="createNewMsproduct"> New Stock</a>
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
                                <th>Category</th>
                                <th>Company Tenant</th>
                                <th>Tenant</th>                                                                                 
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
                            <form id="inputForm" name="inputForm" class="form-horizontal" enctype="multipart/form-data">
                                <input type="hidden" name="key_id" id="key_id">
            
                                <!-- Row container to hold two columns -->
                                <div class="row">
                                    <!-- First Column -->
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="cpnyid" class="col-sm-3 control-label text-right">Company</label>
                                            <div class="col-sm-9">
                                                <select class="form-control select2" id="cpnyidx" name="cpnyid" style="width: 100%;" >
                                                    <option selected="selected"></option>
                                                    {{-- @foreach($company as $p)
                                                        <option value="{{ $p->cpnyid }}">{{ $p->cpnyid }}</option>
                                                    @endforeach --}}
                                                    @foreach($usercpny as $p)
                                                        <option value="{{ $p->cpnyid }}" {{ $p->cpnyid == $usercpny2->cpnyid ? 'selected' : '' }}>{{ $p->cpnyid }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group">                                            
                                            <label for="product_source_company" class="col-sm-3 control-label text-right">Nama PT</label>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control" id="product_source_company" name="product_source_company" >
                                            </div>
                                        </div>
            
                                        <div class="form-group">
                                            <label for="product_source_tenant" class="col-sm-3 control-label text-right">Nama Tenant / Event</label>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control" id="product_source_tenant" name="product_source_tenant" >
                                            </div>
                                        </div>           
                                        
            
                                        <div class="form-group">
                                            <label for="product_name" class="col-sm-3 control-label text-right">Product Name</label>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control" id="product_name" name="product_name" >
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="product_name" class="col-sm-3 control-label text-right">UOM</label>
                                            <div class="col-sm-9">
                                                <select class="form-control select2" id="product_uom" name="product_uom" style="width: 100%;" >
                                                    <option selected="selected"></option>
                                                    <option value="PCS">PCS</option>
                                                    <option value="PACK">PACK</option>                                                   
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-sm-3 control-label text-right">Check Expired Date</label>
                                            <div class="col-sm-9">
                                                <input type="hidden" name="product_check_exp" value="0">
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
                                            <label for="statusx" class="col-sm-3 control-label text-right">Category Stock</label>
                                            <div class="col-sm-9">
                                                <select class="form-control select2" id="categoryx" name="product_category" style="width: 100%;" >
                                                    {{-- <option selected="selected"></option>
                                                    @foreach($category as $p)
                                                        <option value="{{ $p->categoryname }}">{{ $p->categoryname }}</option>
                                                    @endforeach --}}
                                                </select>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label for="product_type" class="col-sm-3 control-label text-right">Product Source</label>
                                            <div class="col-sm-9">
                                                <select class="form-control select2" id="product_source_typex" name="product_source_type" style="width: 100%;" >
                                                    <option selected="selected"></option>
                                                    <option value="Event">Event</option>
                                                    <option value="Tenant">Tenant</option>
                                                    <option value="Sponsor">Sponsor</option>
                                                </select>
                                            </div>
                                        </div>
            
                                        <div class="form-group">
                                            <label for="product_value" class="col-sm-3 control-label text-right">Value</label>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control" id="product_value" name="product_value" value="0" oninput="formatNumerator(this)" >
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="product_remark" class="col-sm-3 control-label text-right">Remarks</label>
                                            <div class="col-sm-9">
                                                <textarea class="form-control" rows="3" id="product_remark" name="product_remark" placeholder="Enter Remarks ..." ></textarea>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="inputEmail3" class="col-sm-3 control-label">Status</label>
                                            <div class="col-sm-9">                                                                              
                                                <select class="form-control select2" id="statusx" name="status" style="width: 100%;">
                                                    <option selected="selected"></option>
                                                    <option value="A">Active</option>
                                                    <option value="X">In Active</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>     
                                
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

    function formatNumerator(input) {
        // Hapus semua karakter selain angka
        let value = input.value.replace(/\D/g, '');

        // Tambahkan format separator ribuan
        value = value.replace(/\B(?=(\d{3})+(?!\d))/g, ',');

        // Tampilkan nilai yang telah diformat
        input.value = value;
    }
 
    function formatNumeratorValue(value) {
        // Konversi nilai ke float untuk memastikan format numerik
        let numericValue = parseFloat(value);

        // Jika nilai valid, tambahkan separator ribuan
        if (!isNaN(numericValue)) {
            return numericValue.toLocaleString('en-US', { maximumFractionDigits: 0 });
        }

        // Jika nilai tidak valid, kembalikan nilai asli
        return value;
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
              {data: 'product_category', name: 'product_category'},
              {data: 'product_source_company', name: 'product_source_company'},  
              {data: 'product_source_tenant', name: 'product_source_tenant'},                      
              {data: 'status', name: 'status'},  
              {data: 'action', name: 'action'},           
          ],
               
        dom:'lBfrtip',
          buttons: ['excel', 'csv', 'pdf', 'copy'],
          lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
          responsive: true,        
        });
       
        $('#createNewMsproduct').click(function () {
            $('#saveBUTTON').html("Save Stock");
            $('#key_id').val('');
            $('#inputForm').trigger("reset");
            $('#modelHeading').html("Create New Stock");
            $('#ajaxModel').modal('show');
            $('#cpnyidx').val(null).trigger('change');    
            $('#product_typex').val(null).trigger('change');                     
            $('#categoryx').val(null).trigger('change');
            $('#product_source_typex').val(null).trigger('change');            
            $('#statusx').val('A').trigger('change');
            $('#product_uom').val(null).trigger('change');
           
        });        
        
        $('body').on('click', '.editMsproduct', function () {
        var key_id = $(this).data('id');
        $.get("{{ route('msproduct.msproduct') }}" +'/' + key_id +'/edit', function (data) {
            $('#modelHeading').html("Edit Stock");
            $('#saveBUTTON').html("Updated Stock");
            $('#ajaxModel').modal('show');

            $('#key_id').val(data.msproduct.id);
            $('#product_source_company').val(data.msproduct.product_source_company);
            $('#product_source_tenant').val(data.msproduct.product_source_tenant);                  
            $('#cpnyidx').val(data.msproduct.cpnyid).trigger('change');    
            $('#product_name').val(data.msproduct.product_name); 
            // $('#product_check_exp').val(data.msproduct.product_check_exp);       
            $('input[name="product_check_exp"]').prop('checked', data.msproduct.product_check_exp == 1);
            $('#product_typex').val(data.msproduct.product_type).trigger('change');
            $('#categoryx').val(data.msproduct.product_category).trigger('change');
            $('#product_source_typex').val(data.msproduct.product_source_type).trigger('change');
            // $('#product_value').val(data.msproduct.product_value); 
            $('#product_value').val(formatNumeratorValue(data.msproduct.product_value));

            $('#product_uom').val(data.msproduct.product_uom).trigger('change');
            $('#product_remark').val(data.msproduct.product_remark); 
            $('#statusx').val(data.msproduct.status).trigger('change');
        })
        });        
        
        $('#saveBUTTON').click(function(e) {
            e.preventDefault();

            $('#product_value').val(function(index, value) {
                return value.replace(/,/g, '');
            });

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
        $('#categoryx').select2({
            width: '100%',
            placeholder: 'Select a Category'
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

        // Validasi Product UOM
        if ($('#product_uom').val().trim() === '') {
            $('#product_uom').after('<span class="error-message" style="color: red;">Product UOM is required</span>');
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
<script>
    $(document).ready(function () {
        function loadCategories(selectedType) {
            let categoryDropdown = $('#categoryx'); // Ambil elemen select category

            // Kosongkan pilihan sebelumnya
            categoryDropdown.empty();

            if (!selectedType) {
                return; // Jika tidak ada tipe produk, jangan jalankan request
            }

            // Panggil satu request AJAX ke backend
            $.ajax({
                url: "{{ route('category.get') }}",
                type: "GET",
                data: { type: selectedType },
                success: function (data) {
                    categoryDropdown.append('<option value="">Select Category</option>');
                    data.forEach(category => {
                        categoryDropdown.append(`<option value="${category.categoryname}">${category.categoryname}</option>`);
                    });
                    categoryDropdown.trigger('change');
                },
                error: function () {
                    alert("Failed to load category data!");
                }
            });
        }

        // Saat memilih Product Type
        $('#product_typex').change(function () {
            let selectedType = $(this).val(); // Ambil nilai yang dipilih (Voucher atau Product)
            loadCategories(selectedType); // Panggil fungsi load kategori
        });
    });
</script>
    

@endsection

