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
                <a class="btn" style="background-color:#006EF0;color:white;margin-right: 10px"  href="javascript:void(0)" id="createNewMsproduct"> New Setup Aging</a>
                </div>
            </div>      
            <div class="modal-footer" style="border-top:none; border-radius:10px;">                    
               
            </div>
            <div class="box">               
                <div class="box-body">
                    <table class="table table-bordered data-table">
                        <thead>
                            <tr>                                
                                <th>Description</th>   
                                <th>Start Age</th>
                                <th>End Age</th>     
                                <th>Order Age</th>                                                                                                               
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
                                <div class="row">
                                    <!-- First Column -->
                                    <div class="col-md-6">                                       
                                        <div class="form-group">                                            
                                            <label for="age_descr" class="col-sm-3 control-label text-right">Age Description</label>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control" id="age_descr" name="age_descr" >
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="start_age" class="col-sm-3 control-label text-right">Start Age</label>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control" id="start_age" name="start_age" >
                                            </div>
                                        </div>      
                                        <div class="form-group">
                                            <label for="end_age" class="col-sm-3 control-label text-right">End Age</label>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control" id="end_age" name="end_age" >
                                            </div>
                                        </div>                                        
                                    </div>                                    
            
                                    <div class="col-md-6">        
                                        <div class="form-group">
                                            <label for="order_age" class="col-sm-3 control-label text-right">Order Age</label>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control" id="order_age" name="order_age" >
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
    $(function () {   
        $.ajaxSetup({
          headers: {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          }
        });   
      var table = $('.data-table').DataTable({
        "order": [[3, "Asc"]],
          processing: true,
          serverSide: true,
          ajax: {
            url: "{{ route('setupaging.setupaging') }}",             
         
          },
          columns: [            
              {data: 'age_descr', name: 'age_descr'}, 
              {data: 'start_age', name: 'start_age'}, 
              {data: 'end_age', name: 'end_age'},   
              {data: 'order_age', name: 'order_age'},                           
              {data: 'status', name: 'status'},  
              {data: 'action', name: 'action'},           
          ],
               
        dom:'lBfrtip',
          buttons: ['excel', 'csv', 'pdf', 'copy'],
          lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
          responsive: true,        
        });
       
        $('#createNewMsproduct').click(function () {
            $('#saveBUTTON').html("Save Setup Aging");
            $('#key_id').val('');
            $('#inputForm').trigger("reset");
            $('#modelHeading').html("Create Setup Aging");
            $('#ajaxModel').modal('show');        
            $('#statusx').val('A').trigger('change');
          
           
        });        
        
        $('body').on('click', '.editMsproduct', function () {
        var key_id = $(this).data('id');
        $.get("{{ route('setupaging.setupaging') }}" +'/' + key_id +'/edit', function (data) {
            $('#modelHeading').html("Edit Aging");
            $('#saveBUTTON').html("Updated Aging");
            $('#ajaxModel').modal('show');

            $('#key_id').val(data.setupaging.id);
            $('#age_descr').val(data.setupaging.age_descr);
            $('#start_age').val(data.setupaging.start_age);        
            $('#end_age').val(data.setupaging.end_age); 
            $('#order_age').val(data.setupaging.order_age);           
            $('#statusx').val(data.setupaging.status).trigger('change');
        })
        });        
        
        $('#saveBUTTON').click(function(e) {
            e.preventDefault();           
            // Memeriksa validasi HTML5 browser sebelum mengirim form
            var form = $('#inputForm')[0];
            
            if (validateForm()) {
                $(this).html('Sending..');

                var formData = new FormData(form);

                $.ajax({
                    data: formData,
                    contentType: false,   
                    processData: false,
                    url: "{{ route('setupaging.save_aging') }}",
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
                       
    });
    
  </script>


<script>
    // Fungsi validasi
    function validateForm() {
        let isValid = true;

        // Reset pesan error
        $('.error-message').remove();
       
        if ($('#age_descr').val().trim() === '') {
            $('#age_descr').after('<span class="error-message" style="color: red;">Description is required</span>');
            isValid = false;
        }
       
        if ($('#start_age').val().trim() === '') {
            $('#start_age').after('<span class="error-message" style="color: red;">Start Aging is required</span>');
            isValid = false;
        }
      
        if ($('#end_age').val().trim() === '') {
            $('#end_age').after('<span class="error-message" style="color: red;">End Aging is required</span>');
            isValid = false;
        }
       
        if ($('#order_age').val().trim() === '') {
            $('#order_age').after('<span class="error-message" style="color: red;">Order is required</span>');
            isValid = false;
        }
              
        return isValid;
    }
    
</script>


<script>
    // Batasi input hanya angka (0-9)
    function allowOnlyNumbers(selector) {
        $(selector).on('keypress', function (e) {
            let charCode = (e.which) ? e.which : e.keyCode;
            // Hanya izinkan angka (0–9)
            if (charCode < 48 || charCode > 57) {
                e.preventDefault();
            }
        });

        $(selector).on('paste', function (e) {
            let pastedData = e.originalEvent.clipboardData.getData('text');
            if (!/^\d+$/.test(pastedData)) {
                e.preventDefault();
            }
        });
    }

    // Terapkan ke field yang diinginkan
    allowOnlyNumbers('#start_age');
    allowOnlyNumbers('#end_age');
    allowOnlyNumbers('#order_age');

</script>
    

@endsection

