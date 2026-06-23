@extends('layouts.template')

@section('header_scripts')
<style>
    .modal-lg {
        max-width: 900px;
    }
    .modal-body {
        max-height: 70vh;
        overflow-y: auto;
    }
</style>


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
            <div class="box">               
                <div class="box-body">
                    <table class="table table-bordered data-table">
                        <thead>
                            <tr>       
                                <th>CpnyID</th>
                                <th>ProductID</th>
                                <th>Name</th>     
                                <th>Expired Date</th>
                                <th>Target Date</th>
                                <th>Stock</th>      
                                <th>Action</th>                                                         
                            </tr>
                        </thead>
                        <tbody>
                        </tbody> 
                    </table>
                </div>                
            </div>
            <!-- Modal Detail -->
            <div class="modal fade" id="productDetailModal" tabindex="-1" role="dialog" aria-labelledby="productDetailModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="productDetailModalLabel">Product Details</h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        </div>
                        <div class="modal-body">
                            <table class="table table-bordered" id="detailTable">
                                <thead>
                                <tr>
                                    <th>Company</th>
                                    <th>Warehouse</th>
                                    <th>Product ID</th>
                                    <th>Expired Date</th>
                                    <th>Target Date</th>
                                    <th>Stock</th>
                                </tr>
                                </thead>
                                <tbody></tbody>
                            </table>         
                            <hr>
                            <form id="updateTargetDateForm">
                                <div class="form-row align-items-center">    
                                    <input type="hidden" class="form-control" id="updateProductId" name="product_id" readonly>
                                    <input type="hidden" class="form-control" id="expiredDateHidden" name="expired_date" readonly>
                                    <div class="form-group col-md-4">
                                        <label for="newTargetDate">New Target Date</label>
                                        <input type="date" class="form-control" id="newTargetDate" name="target_date" required>
                                    </div>
                                    <div class="form-group col-md-4" style="margin-top: 20px;">
                                        <button type="submit" class="btn btn-success">Update Target Date</button>
                                    </div>
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
        "order": [[0, "desc"]],
          processing: true,
          serverSide: true,
          ajax: {
            url: "{{ route('producttarget.producttarget') }}",             
         
          },
          columns: [                          
              {data: 'cpnyid', name: 'cpnyid'}, 
              {data: 'product_id', name: 'product_id'}, 
              {data: 'product_name', name: 'product_name'},   
              {data: 'expired_date', name: 'expired_date'},
              {data: 'target_date', name: 'target_date'},  
              {data: 'stock', name: 'stock'},      
              {data: 'action', name: 'action'},           
          ],
               
        dom:'lBfrtip',
          buttons: ['excel', 'csv', 'pdf', 'copy'],
          lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
          responsive: true,        
        });    
       
        
    });
    
</script>
<script type="text/javascript">
    $(document).on('click', '.detailBtn', function () {
        var productId = $(this).data('id');
        
        $.ajax({
            url: '/producttarget/detail/' + productId,
            type: 'GET',
            success: function (data) {
                var tbody = '';

                if (data.length > 0) {
                    data.forEach(function(item) {
                        tbody += '<tr>'+
                                    '<td>' + item.cpnyid + '</td>'+
                                    '<td>' + item.whs_id + '</td>'+
                                    '<td>' + item.product_id + '</td>'+
                                    '<td>' + item.expired_date + '</td>'+
                                    '<td>' + item.target_date + '</td>'+
                                    '<td>' + item.qty_available + '</td>'+
                                '</tr>';
                    });

                    // Isi input readonly product_id
                    $('#updateProductId').val(data[0].product_id);
                    $('#expiredDateHidden').val(data[0].expired_date);
                } else {
                    tbody = '<tr><td colspan="3" class="text-center">No data found</td></tr>';
                    $('#updateProductId').val(''); // kosongkan jika tidak ada data
                }

                $('#detailTable tbody').html(tbody);
                $('#productDetailModal').modal('show');
            }

        });
    });

    // Submit update form
    // $('#updateTargetDateForm').submit(function(e) {
    //     e.preventDefault();
    //     var formData = $(this).serialize();

    //     $.ajax({
    //         url: '/producttarget/update-target-date',
    //         type: 'POST',
    //         data: formData,
    //         success: function(response) {
    //             alert('Target date updated successfully!');
    //             $('#updateTargetDateForm')[0].reset();
    //             $('.detailBtn[data-id="' + $('#updateProductId').val() + '"]').click(); // reload data
    //         },
    //         error: function(xhr) {
    //             alert('Update failed!');
    //         }
    //     });
    // });
    $('#updateTargetDateForm').submit(function(e) {
        e.preventDefault();

        // Ambil nilai target_date dan expired_date
        var targetDate = new Date($('#newTargetDate').val());
        var expiredDate = new Date($('#expiredDateHidden').val());
      
        // Validasi: target_date tidak boleh > expired_date
        if (targetDate > expiredDate) {
            alert('❌ Target Date tidak boleh lebih dari Expired Date!');
            return;
        }

        var formData = $(this).serialize();

        $.ajax({
            url: '/producttarget/update-target-date',
            type: 'POST',
            data: formData,
            success: function(response) {
                alert('✅ Target date updated successfully!');
                $('#updateTargetDateForm')[0].reset();
                $('.detailBtn[data-id="' + $('#updateProductId').val() + '"]').click(); // reload data
            },
            error: function(xhr) {
                alert('❌ Update failed!');
            }
        });
    });

</script>


@endsection

