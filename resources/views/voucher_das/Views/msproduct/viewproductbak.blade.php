@extends('layouts.template')
@section('header_scripts')
<style>

    .box-body{
        padding:5px;
    }
</style>

<meta name="csrf-token" content="{{ csrf_token() }}">

@endsection
@section('content')
<div class="content-wrapper" style="background-color: #051931;">
    <!-- Content Header (Page header) -->
    <section class="content-header">
           
        <ol class="breadcrumb">
            <li><a href="#" style="color: white;font-size:13px"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active" style="color: white;font-size:13px">View Product</li>

        </ol>
        <div class="box-header" style="display: flex; justify-content: between; margin-left: 0.5rem;">

            
        </div>
    </section>
    <!-- Main content -->
    <section class="content">

        <!-- Main row -->
        <div class="row">
            <!-- Left col -->
            <div class="col-md-7">
                <!-- Request User -->
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Tenant Information</h3>

                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
                            </button>
                            <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
                        </div>
                    </div>

                    <div class="box-body no-padding" style="height: 70px; overflow-y: auto;">
                        <div class="box-body">
                            <div class="form-group">
                                <label for="inputEmail3" class="col-sm-2 control-label">Nama PT</label>
                                <div class="col-sm-4">
                                    <input type="text" class="form-control" value="{{ $msproduct->product_source_company }}" readonly>
                                </div>
                                <label for="inputEmail3" class="col-sm-2 control-label">Nama Tenant</label>
                                <div class="col-sm-4">
                                    <input type="text" class="form-control" value="{{ $msproduct->product_source_tenant }}" readonly>
                                </div>
                                
                            </div>
                        </div>              
                    </div>
                </div>
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Item Information</h3>

                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
                            </button>
                            <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
                        </div>
                    </div>

                    <div class="box-body no-padding" style="height: 270px; overflow-y: auto;">
                        <div class="box-body">
                            <div class="form-group">
                                <label for="inputEmail3" class="col-sm-2 control-label">Product ID</label>
                                <div class="col-sm-4">
                                    <input type="text" class="form-control" value="{{ $msproduct->product_id }}" readonly>
                                </div>
                                <label for="inputEmail3" class="col-sm-2 control-label">Nama Product</label>
                                <div class="col-sm-4">
                                    <input type="text" class="form-control" value="{{ $msproduct->product_name }}" readonly>
                                </div>                                
                            </div>                            
                        </div>
                        <div class="box-body">
                            <div class="form-group">
                                <label for="inputEmail3" class="col-sm-2 control-label">Type</label>
                                <div class="col-sm-4">
                                    <input type="text" class="form-control" value="{{ $msproduct->product_type }}" readonly>
                                </div>
                                <label for="inputEmail3" class="col-sm-2 control-label">Site</label>
                                <div class="col-sm-4">
                                    <input type="text" class="form-control" value="{{ $msproduct->cpnyid }}" readonly>
                                </div>                                
                            </div>                            
                        </div>     
                        <div class="box-body">
                            <div class="form-group">
                                <label for="inputEmail3" class="col-sm-2 control-label">Category</label>
                                <div class="col-sm-4">
                                    <input type="text" class="form-control" value="{{ $msproduct->product_category }}" readonly>
                                </div>
                                <label for="inputEmail3" class="col-sm-2 control-label">Value</label>
                                <div class="col-sm-4">
                                    <input type="text" class="form-control" value="{{ $msproduct->product_value }}" readonly>
                                </div>                                
                            </div>                            
                        </div>              
                        <div class="box-body">
                            <div class="form-group">
                                <label for="inputEmail3" class="col-sm-2 control-label">Check Expired Date</label>
                                <div class="col-sm-4">
                                    <input type="checkbox" value="1" {{ $msproduct->product_check_exp == 1 ? 'checked' : '' }}  disabled>
                                </div>
                                <label for="inputEmail3" class="col-sm-2 control-label">UOM</label>
                                <div class="col-sm-4">
                                    <input type="text" class="form-control" value="{{ $msproduct->product_uom }}" readonly>
                                </div>                                
                            </div>                            
                        </div> 
                        <div class="box-body">
                            <div class="form-group">
                                <label for="inputEmail3" class="col-sm-2 control-label">Remaks</label>
                                <div class="col-sm-10">
                                    <textarea class="form-control" rows="3" readonly>{{ $msproduct->product_remark }}</textarea>
                                   
                                </div>                       
                            </div>                            
                        </div>    
                    </div>
                </div>

            </div>


            <div class="col-md-5">

                <div class="box box-info">
                    <div class="box-header with-border">
                        <h3 class="box-title">Information Detail</h3>
                        <div class="box-tools pull-right">
                            <a href="javascript:void(0)" id="openAddModal" class="btn mb-1 btn-danger">ADD</a>
                            {{-- <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
                            </button>
                            <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button> --}}
                        </div>
                    </div>
                    <!-- /.box-header -->
                    <div class="box-body">
                        {{-- <div class="table-responsive" style="height: 140px; overflow-y: auto;"> --}}
                        <div class="table-responsive">
                            <table class="table no-margin">
                                <thead>
                                    <tr>
                                        <th style="width: 15%;">Qty</th>                                        
                                        <th style="width: 20%;">Expired Date</th>
                                        <th style="width: 30%;">Gudang</th>
                                                                              
                                    </tr>
                                </thead>
                                <tbody>    
                                    @foreach($msproductdetail as $detail)
                                    <tr>
                                        <td>{{ $detail->qty_available }}</td>
                                        <td>{{ $detail->expired_date }}</td>
                                        <td>{{ $detail->whs_id }}</td>
                                    </tr>
                                    @endforeach                               
                                </tbody>
                            </table>
                        </div>
                    </div>                   
                </div>

                <div class="box box-info">
                    <div class="box-header with-border">
                        <h3 class="box-title">Attachement</h3>
                        <div class="box-tools pull-right">
                            <a href="javascript:void(0)" id="openAddAttach" class="btn mb-1 btn-danger">ADD</a>
                            {{-- <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
                            </button>
                            <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button> --}}
                        </div>
                    </div>
                    <!-- /.box-header -->
                    <div class="box-body">
                        {{-- <div class="table-responsive" style="height: 140px; overflow-y: auto;"> --}}
                        <div class="table-responsive">
                            <table class="table no-margin">
                                <thead>
                                    <tr>
                                        <th style="width: 30%;">Filename</th>                                        
                                        <th style="width: 20%;">Created</th>
                                        <th style="width: 20%;">Date</th>
                                                                              
                                    </tr>
                                </thead>
                                <tbody>    
                                    @foreach($attachment as $detailatt)
                                    <tr>
                                        <td><a href="{{ url('/attachment') }}/{{ ($detailatt->created_at)->year }}/{{ $detailatt->attachfile }}" class="mailbox-attachment-name" target="_blank"><i class="
                                            <?php if ($detailatt->extention == 'pdf') {
                                                echo 'fa fa-file-pdf-o';
                                            } else if ($detailatt->extention == 'doc' or $detailatt->extention == 'docx') {
                                                echo 'fa fa-file-word-o';
                                            } else if ($detailatt->extention == 'xls' or $detailatt->extention == 'xlsx') {
                                                echo 'fa fa-file-excel-o';
                                            } else {
                                                echo 'fa fa-file-image-o';
                                            }; ?>"></i> {{ $detailatt->name }}</a></td>
                                        <td>{{ $detailatt->created_user }}</td>
                                        <td>{{ $detailatt->created_at }}</td>
                                    </tr>
                                    @endforeach                               
                                </tbody>
                            </table>
                        </div>
                    </div>                   
                </div>

            </div>
            
            <div class="modal fade" id="ajaxModel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="modelHeading">Add Product Details</h4>
                        </div>
                        <div class="modal-body">
                            <form id="inputForm" name="inputForm" class="form-horizontal" enctype="multipart/form-data">
                            
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <input type="hidden" name="product_id" id="product_id" value="{{ $msproduct->product_id }}">

                                <table class="table table-bordered" id="dynamicTable">
                                    <thead>
                                        <tr>
                                            <th style="width: 20%;">Qty</th>                                        
                                            <th style="width: 20%;">Expired Date</th>
                                            <th style="width: 40%;">Gudang</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><input type="text" name="addmore[0][qty]" placeholder="Enter Qty" class="form-control" /></td>
                                            <td><input type="date" name="addmore[0][expired_date]" class="form-control" /></td>
                                            <td>
                                                <select class="form-control select2" name="addmore[0][source_whs]" style="width: 100%;" required>
                                                    <option selected="selected"></option>
                                                    @foreach($mswhs as $p)
                                                    <option value="{{ $p->whs_id }}">{{ $p->whs_id }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td><button type="button" name="add" id="addRow" class="btn btn-success">Add</button></td>
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

            <div class="modal fade" id="ajaxModelattach" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="modelHeading">Add Attachment</h4>
                        </div>
                        <div class="modal-body">
                            <form id="inputFormattach" name="inputForm" class="form-horizontal" enctype="multipart/form-data">
                            
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <input type="hidden" name="product_id" id="product_id" value="{{ $msproduct->product_id }}">

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
                                    <button type="submit" class="btn" style="background-color:#006EF0;color:white" id="saveBUTTONattach" value="create">Save</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            

        </div>
        <!-- /.row -->
    </section>
    <!-- /.content -->


</div>


@endsection


@section('footer_scripts')
<script>
$(document).ready(function () {
    var i = 0;

    // Add new row in modal
    $('#addRow').click(function () {
        i++;
        $('#dynamicTable').append('<tr id="row' + i + '"><td><input type="text" name="addmore[' + i + '][qty]" placeholder="Enter Qty" class="form-control" /></td><td><input type="date" name="addmore[' + i + '][expired_date]" class="form-control" /></td><td><select class="form-control select2" name="addmore[' + i + '][source_whs]" style="width: 100%;" required><option selected="selected"></option>@foreach($mswhs as $p)<option value="{{ $p->whs_id }}">{{ $p->whs_id }}</option>@endforeach</select></td><td><button type="button" name="remove" id="' + i + '" class="btn btn-danger btn_remove">Del</button></td></tr>');
        $('.select2').select2();
    });

    // Remove row
    $(document).on('click', '.btn_remove', function () {
        var button_id = $(this).attr("id");
        $('#row' + button_id + '').remove();
    });

    // Show modal when ADD is clicked
    $('#openAddModal').click(function () {
        $('#inputForm').trigger("reset");
        $('#ajaxModel').modal('show');
    });

    // Save data from modal
    $('#saveBUTTON').click(function (e) {
        e.preventDefault();
        var formData = new FormData($('#inputForm')[0]);
        if (validateDynamicTable()) {
            $.ajax({
                data: formData,
                url: "{{ route('viewproduct.save_viewproduct') }}",
                type: "POST",
                dataType: 'json',
                contentType: false,
                processData: false,
                success: function (data) {
                    $('#inputForm').trigger("reset");
                    $('#ajaxModel').modal('hide');
                    location.reload(); // Reload page to show new data
                },
                error: function (data) {
                    console.log('Error:', data);
                }
            });
        } else {
            // Trigger form validation
            console.log("Form tidak valid.");
        }
    });
});

    function validateDynamicTable() {
        var isValid = true;

        // Loop through each row in the dynamic table
        $('#dynamicTable tbody tr').each(function() {
            var qty = $(this).find('input[name^="addmore"]').filter('[name*="[qty]"]').val();            
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
        var j = 0;
    
        // Add new row in modal
        $('#addRow2').click(function () {
            j++;
            $('#dynamicTable2').append('<tr id="row' + j + '"><td><input type="file" name="attachment[' + j + ']" class="form-control" /></td><td><button type="button" name="remove" id="' + j + '" class="btn btn-danger btn_remove2">Del</button></td></tr>');
        });

        // Remove row
        $(document).on('click', '.btn_remove2', function() {
            var button_id = $(this).attr("id");
            $('#row' + button_id + '').remove();
        });
    
        // Show modal when ADD is clicked
        $('#openAddAttach').click(function () {
            $('#inputFormattach').trigger("reset");
            $('#ajaxModelattach').modal('show');
        });
    
        // Save data from modal
        $('#saveBUTTONattach').click(function (e) {
            e.preventDefault();
            var formData = new FormData($('#inputFormattach')[0]);           
                $.ajax({
                    data: formData,
                    url: "{{ route('viewproduct.save_viewproductattach') }}",
                    type: "POST",
                    dataType: 'json',
                    contentType: false,
                    processData: false,
                    success: function (data) {
                        $('#inputFormattach').trigger("reset");
                        $('#ajaxModelattach').modal('hide');
                        location.reload(); // Reload page to show new data
                    },
                    error: function (data) {
                        console.log('Error:', data);
                    }
                });
           
            });
         });
    
       
    </script>

@endsection