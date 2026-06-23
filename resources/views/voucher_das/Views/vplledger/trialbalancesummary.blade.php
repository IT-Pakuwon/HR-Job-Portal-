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
    <h1 style="color: white">{{ $tittle }}</h1>
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
                        <label for="perpost" class="control-label">Perpost:</label>
                        <select class="form-control select2" id="perpost" name="perpost">
                            <option selected="selected" value=""></option>
                            @foreach($perpost as $p)
                                <option value="{{ $p->perpost_year }}{{ $p->perpost_month }}">{{ $p->perpost_year }}{{ $p->perpost_month }}</option>
                            @endforeach
                        </select>
                    </div>               
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
                        <label for="whs_id" class="control-label">Warehouse:</label>
                        <select class="form-control select2" id="whs_id" name="whs_id">
                            <option selected="selected" value=""></option>
                            {{-- @foreach($warehouse as $p)
                                <option value="{{ $p->whs_id }}">{{ $p->whs_id }}</option>
                            @endforeach --}}
                        </select>
                    </div>             
                    
                </div>
            </div>
                    
            <div class="box-body">
                <table class="table table-bordered data-table">
                    <thead>
                        <tr>
                            <th rowspan="2">Perpost</th>
                            <th rowspan="2">Company</th>
                            <th rowspan="2">WhsID</th>
                            <th rowspan="2">ProductID</th>
                            <th rowspan="2">Expired Date</th>                                                                                   
                            <th rowspan="2">Name</th>                               
                            <th colspan="11">Stock</th>                               
                        </tr>
                        <tr class="sub-header">   
                            <th>Begin Qty</th>                            
                            <th>In</th>
                            <th>Transfer</th>
                            <th>Out<br>Redeem PG</th>
                            <th>Out<br>Entertaiment</th>
                            <th>Out<br>Management</th>
                            <th>Out<br>Sales Promo</th>                               
                            <th>Out<br>Dijual</th>
                            <th>Out<br>Adjusment</th>   
                            <th>End Qty</th>          
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
                url: "{{ route('trialbalancesummary.trialbalancesummary') }}",
                data: function (d) {
                    d.cpnyid = $('#cpnyid').val();
                    d.perpost = $('#perpost').val();
                    d.whs_id = $('#whs_id').val();
                }
            },
            columns: [          
                {data: 'perpost', name: 'perpost'},
                {data: 'cpnyid', name: 'cpnyid'},
                {data: 'whs_id', name: 'whs_id'}, 
                {data: 'product_id', name: 'product_id'},             
                {data: 'expired_date', name: 'expired_date'},       
                {data: 'product_name', name: 'product_name'},             
                {data: 'begqty', name: 'begqty'},                                    
                {data: 'qtyin', name: 'qtyin'},    
                {data: 'qty_transfer', name: 'qty_transfer'},
                {data: 'qtyout_redeempg', name: 'qtyout_redeempg'}, 
                {data: 'qtyout_entertaiment', name: 'qtyout_entertaiment'},        
                {data: 'qtyout_management', name: 'qtyout_management'}, 
                {data: 'qtyout_salespromo', name: 'qtyout_salespromo'}, 
                {data: 'qtyout_dijual', name: 'qtyout_dijual'},
                {data: 'qtyout_adjustment', name: 'qtyout_adjustment'},                  
                {data: 'endqty', name: 'endqty'},
            ],      
            dom:'lBfrtip',
            buttons: ['excel', 'csv', 'pdf', 'copy'],
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            responsive: true,            
        });

        // Event listener untuk filter cpnyid & perpost
        $('#cpnyid, #perpost,#whs_id').change(function () {
            table.ajax.reload();
        });
    });    
</script>
<script>
    $('#cpnyid').change(function () {
        let cpnyid = $(this).val();
        
        // Kosongkan warehouse terlebih dahulu
        $('#whs_id').empty().append('<option value="">Loading...</option>');
        
        if(cpnyid){
            $.ajax({
                url: `/get-warehouse-by-company/${cpnyid}`,
                type: 'GET',
                success: function(data) {
                    $('#whs_id').empty().append('<option value="">Select Warehouse</option>');
                    $.each(data, function(index, warehouse) {
                        $('#whs_id').append(`<option value="${warehouse.whs_id}">${warehouse.whs_id}</option>`);
                    });
                },
                error: function() {
                    $('#whs_id').empty().append('<option value="">Error loading data</option>');
                }
            });
        } else {
            $('#whs_id').empty().append('<option value="">Select Company First</option>');
        }

        // Reload DataTables setelah update filter
        $('.data-table').DataTable().ajax.reload();
    });

</script>
@endsection


