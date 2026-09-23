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
            <li class="active" style="color: white">Warehouse</li>
        </ol>
    </section>
    <div class="box-header" style="display: flex; justify-content: between; margin-left: 0.5rem;">
        <a href="{{ url('/mswhs') }}" style="margin-right: 10px; background-color: #006EF0; color: white;" class="btn mb-1">Warehouse</a>
        <div style="display: flex;">
            <a href="{{ url('/mswhsdept') }}" style="margin-right: 10px; background-color: #ffffff; color: black;" class="btn mb-1">Warehouse Dept</a>
            <a href="{{ url('/mssource') }}" style="margin-right: 10px; background-color: #EA002F; color: white;" class="btn mb-1">Source Receive</a>
        </div>
    </div>

    <!-- Main content -->
    <section class="content" style="margin-top:2rem">
        <div class="box box-info" style=" border-top:none; border-radius:10px;"> 
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h1 style="color: Black; padding-left:2rem; font-size:2rem;">Warehouse</h1>
                <div>
                <a class="btn" style="background-color:#006EF0;color:white;margin-right: 10px"  href="javascript:void(0)" id="createNewMswhs"> New Whs</a>
                </div>
            </div>      
            <div class="modal-footer" style="border-top:none; border-radius:10px;">                    
               
            </div>
            <div class="box">               
                <div class="box-body">
                    <table class="table table-bordered data-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>WHS ID</th>   
                                <th>CpnyID</th>
                                <th>WHS Name</th>
                                <th>WHS Type</th>                                                        
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
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="modelHeading"></h4>
                        </div>
                        <div class="modal-body">
                            <form id="inputForm" name="inputForm" class="form-horizontal">
                                <input type="hidden" name="key_id" id="key_id">                                               
                                <div class="form-group">
                                    <div class="col-md-12">
                                    <label for="inputEmail3" class="col-sm-3 control-label">Whs ID</label>
                                        <div class="col-sm-9">                                                                              
                                            <input type="text" class="form-control" id="whs_id" name="whs_id" required>
                                        </div>
                                    </div>
                                </div>  
                                <div class="form-group">
                                    <div class="col-md-12">
                                        <label for="cpnyid" class="col-sm-3 control-label">Company</label>
                                        <div class="col-sm-9">
                                            <select class="form-control select2" id="cpnyidx" name="cpnyid" style="width: 100%;" required>
                                                <option selected="selected"></option>
                                                @foreach($company as $p)
                                                    <option value="{{ $p->cpnyid }}">{{ $p->cpnyid }}</option>
                                                @endforeach
                                            </select>      
                                        </div>      
                                    </div>
                                </div> 
                                <div class="form-group">
                                    <div class="col-md-12">
                                        <label for="inputEmail3" class="col-sm-3 control-label">Whs Name</label>
                                        <div class="col-sm-9">                                                                              
                                            <input type="text" class="form-control" id="whs_name" name="whs_name" required>
                                        </div>
                                    </div>
                                </div> 
                                <div class="form-group">                                                                        
                                    <div class="col-md-12">
                                        <label for="inputEmail3" class="col-sm-3 control-label">Whs Type</label>
                                        <div class="col-sm-9">                                                                              
                                            <select class="form-control select2" id="whs_typex" name="whs_type" style="width: 100%;">
                                                <option selected="selected"></option>
                                                <option value="Parent">Parent</option>
                                                <option value="Child">Child</option>
                                            </select>
                                        </div>
                                    </div>                                    
                                </div> 
                                <div class="form-group">
                                    <div class="col-md-12">
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
                                <div class="modal-footer">
                                    <button type="button" class="btn pull-left" style="background-color:#EA002F;color:white"  data-dismiss="modal">Close</button>
                                    <button type="submit" class="btn" style="background-color:#006EF0;color:white"  id="saveBUTTON" value="create"></button>
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
            url: "{{ route('mswhs.mswhs') }}",             
         
          },
          columns: [
                {data: 'DT_RowIndex', name: 'DT_RowIndex'},
              {data: 'whs_id', name: 'whs_id'}, 
              {data: 'cpnyid', name: 'cpnyid'}, 
              {data: 'whs_name', name: 'whs_name'}, 
              {data: 'whs_type', name: 'whs_type'},             
              {data: 'status', name: 'status'},  
              {data: 'action', name: 'action'},           
          ],
               
        dom:'lBfrtip',
          buttons: ['excel', 'csv', 'pdf', 'copy'],
          lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
          responsive: true,        
        });
       
        $('#createNewMswhs').click(function () {
            $('#saveBUTTON').html("Save Whs");
            $('#key_id').val('');
            $('#inputForm').trigger("reset");
            $('#modelHeading').html("Create New Whs");
            $('#ajaxModel').modal('show');
            $('#cpnyidx').val(null).trigger('change');
            $('#whs_typex').val(null).trigger('change');
            $('#statusx').val(null).trigger('change');
        });        
        
        $('body').on('click', '.editMswhs', function () {
        var key_id = $(this).data('id');
        $.get("{{ route('mswhs.mswhs') }}" +'/' + key_id +'/edit', function (data) {
            $('#modelHeading').html("Edit Whs");
            $('#saveBUTTON').html("Updated Whs");
            $('#ajaxModel').modal('show');
            $('#key_id').val(data.id);
            $('#whs_id').val(data.whs_id);           
            $('#whs_name').val(data.whs_name);          
            $('#cpnyidx').val(data.cpnyid).trigger('change'); 
            $('#whs_typex').val(data.whs_type).trigger('change'); 
            $('#statusx').val(data.status).trigger('change');
        })
        });        
        
        $('#saveBUTTON').click(function (e) {
            e.preventDefault();
            $(this).html('Sending..');
        
            $.ajax({
            data: $('#inputForm').serialize(),
            url: "{{ route('mswhs.save_whs') }}",
            type: "POST",
            dataType: 'json',
            success: function (data) {
        
                $('#inputForm').trigger("reset");
                $('#ajaxModel').modal('hide');
                table.draw();
            
            },
            error: function (data) {
                console.log('Error:', data);
                $('#saveBUTTON').html('Save Changes');
            }
        });
        });

        $('#cpnyidx').select2({
            width: '100%',
            placeholder: 'Select a Company'
        });
        $('#whs_typex').select2({
            width: '100%',
            placeholder: 'Select a Whs Type'
        });
        $('#statusx').select2({
            width: '100%',
            placeholder: 'Select a Status'
        });
                
    });
    
  </script>
    

@endsection

