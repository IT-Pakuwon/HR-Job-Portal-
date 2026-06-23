@extends('layouts.template')
@section('header_scripts')

<style>
    table {
        border-collapse: collapse;
        width: 100%;
        font-family: Arial, sans-serif;
    }

    th, td {
        border: 1px solid black;
        text-align: center;
        padding: 8px;
    }

    th {
        background-color: #2F4F4F;
        color: white;
    }

    .sub-header {
        background-color: #3C3F41;
        color: white;
        font-weight: bold;
    }
</style>

@endsection

@section('content')

<div class="content-wrapper" style="background-color: #051931;">
  <section class="content-header">
    <h1 style="color: white">Rekap</h1>
      <ol class="breadcrumb">
          <li style="color: white; font-size:13px" ><a href="#" style="color: white"><i class="fa fa-dashboard"></i> Home</a></li>
          <li class="active" style="color: white;font-size:13px">{{$tittle}}</li>
      </ol>
  </section>
          
  
    <!-- Main content -->
    <section class="content" >  
      <div class="box box-info" style="border-top:none; border-radius:10px;">      
                <div class="box-header" style="border-top: none; border-radius: 10px;">
                    <div class="row">                       
                        <div class="col-md-3">
                            <label for="cpnyid" class="control-label">Company:</label>
                            <select class="form-control select2" id="cpnyid" name="cpnyid">
                                <option selected="selected" value=""></option>
                               @foreach($company as $p)
                                    <option value="{{ $p->cpnyid }}">{{ $p->cpnyid }}</option>
                                @endforeach
                            </select>
                        </div>                
                        <div class="col-md-3">
                            <label for="cpnyid" class="control-label">Perpost:</label>
                            <select class="form-control select2" id="perpost" name="perpost">
                                <option selected="selected" value=""></option>
                                @foreach($perpost as $p)
                                    <option value="{{ $p->perpost_year }}{{ $p->perpost_month }}">{{ $p->perpost_year }}{{ $p->perpost_month }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                       
                <div class="box-body">
                    <table class="table table-bordered data-table">
                        <thead>
                            <tr>
                                <th rowspan="2">Company</th>
                                <th rowspan="2">ProductID</th>
                                <th rowspan="2">Name</th>
                                <th rowspan="2">Category</th>
                                <th rowspan="2">Tenant</th>
                                <th rowspan="2">Company Tenant</th>
                                <th rowspan="2">Value</th>
                                <th rowspan="2">Expired Date</th>
                                <th rowspan="2">Perpost</th>
                                <th rowspan="2">Post Date</th>                               
                                <th colspan="7">Stock</th>                               
                            </tr>
                            <tr class="sub-header">
                                <th>Beginning<br>Qty</th>
                                <th>In</th>
                                <th>Out<br>Redeem PG</th>
                                <th>Out<br>Entertaiment</th>
                                <th>Out<br>Management</th>
                                <th>Out<br>Adjustment</th>
                                <th>Ending<br>Qty</th>
                                
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
                url: "{{ route('rekapvoucher.rekapvoucher') }}",
                data: function (d) {
                    d.cpnyid = $('#cpnyid').val();
                    d.perpost = $('#perpost').val();
                }
            },
            columns: [          
                {data: 'cpnyid', name: 'cpnyid'},
                {data: 'product_id', name: 'product_id'},             
                {data: 'product_name', name: 'product_name'},
                {data: 'product_category', name: 'product_category'},
                {data: 'product_source_tenant', name: 'product_source_tenant'},             
                {data: 'product_source_company', name: 'product_source_company'},            
                {data: 'product_value', name: 'product_value'},   
                {data: 'expired_date', name: 'expired_date'},                                  
                {data: 'perpost', name: 'perpost'},  
                {data: 'postdate', name: 'postdate'}, 
                {data: 'begqty', name: 'begqty'},              
                {data: 'qtyin', name: 'qtyin'},    
                {data: 'qtyout_redeempg', name: 'qtyout_redeempg'}, 
                {data: 'qtyout_entertaiment', name: 'qtyout_entertaiment'},        
                {data: 'qtyout_management', name: 'qtyout_management'}, 
                {data: 'qtyout_adjustment', name: 'qtyout_adjustment'}, 
                {data: 'endqty', name: 'endqty'},   
            ],      
            dom:'lBfrtip',
            buttons: ['excel', 'csv', 'pdf', 'copy'],
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            responsive: true,            
        });

        // Event listener untuk filter cpnyid & perpost
        $('#cpnyid, #perpost').change(function () {
            table.ajax.reload();
        });
    });    
</script>
@endsection


