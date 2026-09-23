@extends('layouts.template')

@section('content')

<div class="content-wrapper" style="background-color: #051931;">
  <section class="content-header">
    <h1 style="color: white">List Ledger All</h1>
      <ol class="breadcrumb">
          <li style="color: white; font-size:13px" ><a href="#" style="color: white"><i class="fa fa-dashboard"></i> Home</a></li>
          <li class="active" style="color: white;font-size:13px">{{$tittle}}</li>
      </ol>
  </section>
          
  
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
                            <th>ID</th>
                            <th>Ref No</th>
                            <th>CreateDate</th>   
                            <th>Type</th>    
                            <th>PostDate</th>                                                                        
                            <th>Perpost</th>  
                            <th>Product ID</th>
                            <th>Product Name</th>
                            <th>Qty</th>                                                  
                            <th>Expired Date</th>    
                            <th>Reference Refnbr</th>  
                            <th>Purpose</th>
                            <th>Warehouse</th>                           
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
            url: "{{ route('vplledgerall.vplledgerall') }}",         
            
          },
          columns: [
            {data: 'id', name: 'id', visible: false, searchable: false},
              {data: 'refnbr', name: 'refnbr'},
              {data: 'refdate', name: 'refdate'},             
              {data: 'type', name: 'type'},
              {data: 'postdate', name: 'postdate'},
              {data: 'perpost', name: 'perpost'},             
              {data: 'product_id', name: 'product_id'},            
              {data: 'product_name', name: 'product_name'},   
              {data: 'qty', name: 'qty'},                                  
              {data: 'expired_date', name: 'expired_date'},  
              {data: 'reference_refnbr', name: 'reference_refnbr'}, 
              {data: 'purpose_id', name: 'purpose_id'},              
              {data: 'whs_id', name: 'whs_id'},           
          ],      
        dom:'lBfrtip',
          buttons: ['excel', 'csv', 'pdf', 'copy'],
          lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
          responsive: true, 
          // columnDefs: [
          //   { targets: '_all', defaultContent: '-', className: 'text-left' } // Pastikan kolom kosong diisi dengan tanda '-'
          // ]     
        
      });
  
    });    
  </script>

@endsection

