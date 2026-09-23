@extends('layouts.template')

@section('content')

<div class="content-wrapper" style="background-color: #051931;">
  <section class="content-header">
      <ol class="breadcrumb">
          <li style="color: white; font-size:13px" ><a href="#" style="color: white"><i class="fa fa-dashboard"></i> Home</a></li>
          <li class="active" style="color: white;font-size:13px">{{$tittle}}</li>
      </ol>
  </section>
  
        
  <div class="box-header" style="display: flex; justify-content: between; margin-left: 0.5rem;">
    <a href="{{ url('/addvplreceive') }}" style="margin-right: 10px; background-color: #006EF0; color: white;" class="btn mb-1">Create Receive</a>
    <div style="display: flex;">
        <a href="{{ url('/vplreceive_all') }}" style="margin-right: 10px; background-color: #ffffff; color: black;" class="btn mb-1">All</a>
        <a href="{{ url('/vplreceive_waiting') }}" style="margin-right: 10px; background-color: #FFCD05; color: white;" class="btn mb-1">On Progress</a>
        <a href="{{ url('/vplreceive_rejected') }}" style="margin-right: 10px; background-color: #EA002F; color: white;" class="btn mb-1">Rejected</a>
        <a href="{{ url('/vplreceive_completed') }}" style="margin-right: 10px; background-color: #05A801; color: white;" class="btn mb-1">Completed</a>
    </div>
  </div>

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
                            <th>Doc No</th>
                            <th>Date</th>                           
                            <th>Company</th>
                            <th>Type</th>
                            <th>Tenant</th>                                                   
                            <th>Remark</th>  
                            <th>Waiting</th>                                                  
                            <th>Status</th>                               
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
      var table = $('.data-table').DataTable({
        "order": [[0, "desc"]],
        "responsive": true,
          "autoWidth": false, 
          processing: true,
          serverSide: true,
          ajax: {
            url: "{{ route('vplreceive_waiting.vplreceive_waiting') }}",         
            
          },
          columns: [
              {data: 'receive_id', name: 'receive_id'},
              {data: 'receive_date', name: 'receive_date'},             
              {data: 'cpnyid', name: 'cpnyid'},
              {data: 'receive_type', name: 'receive_type'},
              {data: 'receive_tenant', name: 'receive_tenant'},
              {data: 'receive_remark', name: 'receive_remark'},            
              {data: 'waiting', name: 'waiting'},                                     
              {data: 'status', name: 'status'},             
          ],      
        dom:'lBfrtip',
          buttons: ['excel', 'csv', 'pdf', 'copy'],
          lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
          responsive: true,      
        
      });
  
    });    
  </script>

@endsection

