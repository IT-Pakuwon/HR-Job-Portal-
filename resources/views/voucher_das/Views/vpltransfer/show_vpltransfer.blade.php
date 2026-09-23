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
        <h1 style="color:white">
            Show {{ $transfertype }}
            <small>-</small> 
                <a href="{{ url('/printvpltransfer') }}_{{ $vpltransfer->id }}" button type="button" class="btn mb-1 btn-warning " target="_blank">Print {{ $transfertype }}</a>           
            <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#modal-cancel" style="<?php echo $hiddenx; ?>">Cancel Document</button>
        </h1>       
        <ol class="breadcrumb">
            <li><a href="#" style="color: white;font-size:13px"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active" style="color: white;font-size:13px">Show {{ $transfertype }}</li>
        </ol>       
    </section>
    <!-- Main content -->
    <section class="content">

        <!-- Main row -->
        <div class="row">
            <!-- Left col -->
            <div class="col-md-7">
                <!-- Request User -->
                <div class="box box-success">
                    <div class="box-header with-border">
                        <h3 class="box-title">{{ $transfertype }} Information</h3>

                        <div class="box-tools pull-right">
                            <a href="{{ url('/editvpltransfer') }}_{{ $vpltransfer->id }}" button type="button" hide class="btn mb-1 btn-danger " style="<?php echo $hidden; ?>">Edit</a>
                            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
                            </button>
                            <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
                        </div>
                    </div>

                    <div class="box-body no-padding" style="height: 280px; overflow-y: auto;">
                        <div class="box-body">
                            <div class="form-group">
                                <label for="inputEmail3" class="col-sm-2 control-label">{{ $transfertype }} No</label>
                                <div class="col-sm-4">
                                    <input type="text" class="form-control" value="{{ $vpltransfer->transfer_id }}" readonly>
                                </div>
                                <label for="inputEmail3" class="col-sm-2 control-label">Date</label>
                                <div class="col-sm-4">
                                    <input type="text" class="form-control" value="{{ $vpltransfer->transfer_date }}" readonly>
                                </div>                                
                            </div>                            
                        </div> 
                        <div class="box-body">
                            <div class="form-group">
                                <label for="inputEmail3" class="col-sm-2 control-label">Company</label>
                                <div class="col-sm-4">
                                    <input type="text" class="form-control" value="{{ $vpltransfer->cpnyid }}" readonly>
                                </div>
                                <label for="inputEmail3" class="col-sm-2 control-label">Department</label>
                                <div class="col-sm-4">
                                    <input type="text" class="form-control" value="{{ $vpltransfer->department }}" readonly>
                                </div>                                
                            </div>                            
                        </div> 
                        <div class="box-body">
                            <div class="form-group">
                                <label for="inputEmail3" class="col-sm-2 control-label">User</label>
                                <div class="col-sm-4">
                                    <input type="text" class="form-control" value="{{ $vpltransfer->user }}" readonly>
                                </div>
                                <label for="inputEmail3" class="col-sm-2 control-label">Voucher / Product Type</label>
                                <div class="col-sm-4">
                                    <input type="text" class="form-control" value="{{ $vpltransfer->vp_type == 'V' ? 'VOUCHER' : ($vpltransfer->vp_type == 'P' ? 'PRODUCT' : '') }}" readonly>
                                </div>                                
                            </div>                            
                        </div> 
                        <div class="box-body">
                            <div class="form-group">
                                <label for="inputEmail3" class="col-sm-2 control-label">Status</label>
                                <div class="col-sm-4">
                                    <input type="text" class="form-control" name="status" value="{{ $status_doc }}" readonly>
                                </div> 
                                <label for="inputEmail3" class="col-sm-2 control-label">Type</label>
                                <div class="col-sm-4">
                                    <input type="text" class="form-control" value="{{ $transfertype }}" readonly>
                                </div>                                                          
                            </div>                            
                        </div> 
                        
                        <div class="box-body">
                            <div class="form-group">                               
                                <label for="inputEmail3" class="col-sm-2 control-label">Remark</label>
                                <div class="col-sm-10">  
                                    <textarea class="form-control" rows="3" id="transfer_remark" name="transfer_remark" readonly>{{ $vpltransfer->transfer_remark }}</textarea>
                                </div>                                
                            </div>                            
                        </div>          
                    </div>
                </div>      
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">{{ $transfertype }} Detail</h3>

                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
                            </button>
                            <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
                        </div>
                    </div>

                    <div class="box-body no-padding" style="height: 270px; overflow-y: auto;">
                        <div class="table-responsive">
                            <table class="table no-margin">
                                <thead>
                                    <tr>
                                        <th style="width: 10%;">ProductID</th> 
                                        <th style="width: 10%;">Expired Date</th>
                                        <th style="width: 20%;">Name</th> 
                                        <th style="width: 10%;">Stock</th>   
                                        <th style="width: 10%;">Qty</th>                                     
                                        <th style="width: 15%;">From Gudang</th>
                                        <th style="width: 15%;">To Gudang</th>            
                                    </tr>
                                </thead>
                                <tbody>    
                                    @foreach($vpltransferdetail as $detail)
                                    <tr>
                                        <td>{{ $detail->product_id }}</td>
                                        <td>{{ $detail->expired_date == '1900-01-01' ? 'No Expired' : $detail->expired_date }}</td>
                                        <td>{{ $detail->product_name }}</td>
                                        <td>{{ $detail->qty_available }}</td>
                                        <td>{{ $detail->qty_transfer }}</td>
                                        <td>{{ $detail->from_whs_id }}</td>
                                        <td>{{ $detail->to_whs_id }}</td>
                                    </tr>
                                    @endforeach                               
                                </tbody>
                            </table>
                        </div>    
                    </div>
                </div>              

            </div>


            <div class="col-md-5">

                <div class="box box-info" style="height: 220px; overflow-y: auto;">
                    <div class="box-header with-border">
                        <h3 class="box-title">Approval</h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
                            </button>
                            <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
                        </div>
                    </div>
                    <!-- /.box-header -->
                    <div class="box-body">
                        <div class="table-responsive">
                            {{-- <div class="table-responsive" style="height: 180px; overflow-y: auto;"> --}}
                            <table class="table no-margin">
                                <thead>
                                    <tr>
                                        <th>Level</th>
                                        <th>User</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($t_approval as $p)
                                    <tr>
                                        <td>{{ $p->aprvid }}</td>
                                        <td>{{ $p->name }}</td>
                                        <td>{{ $p->aprvdateafter }}</td>
                                        <td><?php
                                            if ($p->status == 'P') {
                                                echo '<span class="label label-warning">Waiting Approved</span>';
                                            } else if ($p->status == 'A') {
                                                echo '<span class="label label-success">Approved</span>';
                                            } else if ($p->status == 'R') {
                                                echo '<span class="label label-danger">Reject</span>';
                                            } else if ($p->status == 'D') {
                                                echo '<span class="label label-info">Revise</span>';
                                            } else {
                                                echo '';
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="box-footer clearfix">
                        <div class="box-body">
                            <button type="button" class="btn " style="background-color: #2a81ec; color:white"  data-toggle="modal" data-target="<?php echo $popup_approve; ?>">Approve</button>                            
                            <button type="button" class="btn" style="background-color: #40ca3e; color:white" data-toggle="modal" data-target="<?php echo $popup_revise; ?>">Revise</button>
                            <button type="button" class="btn" style="background-color: #e63155; color:white"   data-toggle="modal" data-target="<?php echo $popup_reject; ?>">Reject</button>
                        </div>                    
                    </div>
                </div>

                <div class="box box">
                    <div class="box-header with-border">
                        <h3 class="box-title">Attachment</h3>
                        <div class="box-tools pull-right">                           
                            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
                            </button>
                            <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
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
                                    @foreach($t_attachment as $detailatt)
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
                <div class="box box-warning direct-chat direct-chat-warning" id="msg_load" data-messages="{{ count($t_message) }}">
                    <div class="box-header with-border">
                        <h3 class="box-title">Message</h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                            <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
                        </div>
                    </div>
                    <div class="box-body">                      
                            <div class="direct-chat-messages" style="height: 210px; overflow-y: auto;">                          
                            @foreach($t_message as $msg)
                                @if ($msg->name <> $user->name)
                                    <div class="direct-chat-msg">
                                        <div class="direct-chat-info clearfix">
                                            <span class="direct-chat-name pull-left">{{ $msg->name }}</span>
                                            <span class="direct-chat-timestamp pull-right">{{ $msg->created_at }}</span>
                                        </div>
                                        <img class="direct-chat-img" src="assets/dist/img/msg_left.png" alt="message user image">
                                        <div class="direct-chat-text">
                                            {{ $msg->message }}
                                        </div>
                                    </div>
                                @else
                                    <div class="direct-chat-msg right">
                                        <div class="direct-chat-info clearfix">
                                            <span class="direct-chat-name pull-right">{{ $msg->name }}</span>
                                            <span class="direct-chat-timestamp pull-left">{{ $msg->created_at }}</span>
                                        </div>
                                        <img class="direct-chat-img" src="assets/dist/img/msg_right.png" alt="message user image">
                                        <div class="direct-chat-text">
                                            {{ $msg->message }}
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                    <div class="box-footer">
                        <div class="input-group">
                            <input type="text" id="message" name="message" placeholder="Type Message ..." class="form-control">
                            <span class="input-group-btn">
                                <button id="btnsend" type="submit" class="btn btn-warning btn-flat" onclick="send({{ $vpltransfer->id }})">Send</button>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- popup approve -->
                <div class="modal modal-info fade" id="modal-info">
                    <div class="modal-dialog modal-sm">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span></button>
                                <h4 class="modal-title">Approve</h4>
                            </div>
                            <div class="modal-body">
                                <p>Are you sure want to approve ?</p>
                            </div>
                            <div class="modal-footer">
                                <a href="" class="btn btn-outline pull-left" data-dismiss="modal">Close</a>
                                <form class="form-horizontal" action="{{ url('/approvevpltransfer') }}_{{ $vpltransfer->id }}" method="post">
                                    {{ csrf_field() }}
                                    {{ method_field('PUT') }}                                    
                                    <button type="submit" onclick="closeWindow()" class="btn btn-outline">Approve</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- warning approve -->
                <div class="modal modal-warning fade" id="modal-warning">
                    <div class="modal-dialog modal-sm">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span></button>
                                <h4 class="modal-title">Warning Approve</h4>
                            </div>
                            <div class="modal-body">
                                <p>You Can't Approve !</p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-outline pull-left" data-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Reject approve -->
                <div class="modal modal-danger fade" id="modal-danger">
                    <div class="modal-dialog modal-sm">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span></button>
                                <h4 class="modal-title">Reject</h4>
                            </div>
                            <form class="form-horizontal" action="{{ url('/rejectvpltransfer') }}_{{ $vpltransfer->id }}" method="post">
                                {{ csrf_field() }}
                                {{ method_field('PUT') }}
                                <div class="modal-body">
                                    <p>Are you sure want to reject ?</p>
                                    <textarea class="form-control" rows="3" name="message" placeholder="Enter Reason ..." required></textarea>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-outline pull-left" data-dismiss="modal">Close</button>                                    
                                    <button type="submit" onclick="closeWindow()" class="btn btn-outline">Reject</button>
                                </div>
                            </form>    
                        </div>
                        <!-- /.modal-content -->
                    </div>
                    <!-- /.modal-dialog -->
                </div>

                <!-- Revise approve -->
                <div class="modal modal-success fade" id="modal-success">
                    <div class="modal-dialog modal-sm">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span></button>
                                <h4 class="modal-title">Revise</h4>
                            </div>
                            <form class="form-horizontal" action="{{ url('/revisevpltransfer') }}_{{ $vpltransfer->id }}" method="post">
                                {{ csrf_field() }}
                                {{ method_field('PUT') }}
                                <div class="modal-body">
                                    <p>Are you sure want to revise ?</p>
                                    <textarea class="form-control" rows="3" name="message" placeholder="Enter Reason ..." required></textarea>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-outline pull-left" data-dismiss="modal">Close</button>                                    
                                    <button type="submit" onclick="closeWindow()" class="btn btn-outline">Revise</button>
                                </div>
                            </form>    
                        </div>
                        <!-- /.modal-content -->
                    </div>
                    <!-- /.modal-dialog -->
                </div>

                <!-- Cancel approve -->
                <div class="modal modal-danger fade" id="modal-cancel">
                    <div class="modal-dialog modal-sm">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span></button>
                                <h4 class="modal-title">Cancel Document</h4>
                            </div>
                            <form class="form-horizontal" action="{{ url('/vpltransfercancel') }}_{{ $vpltransfer->id }}" method="post">
                                {{ csrf_field() }}
                                {{ method_field('PUT') }}                               
                                <div class="modal-body">
                                    <p>Are you sure want to Cancel Document ?</p>                                    
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-outline pull-left" data-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn-outline">Cancel</button>
                                </div>
                            </form>    
                        </div>
                        <!-- /.modal-content -->
                    </div>
                    <!-- /.modal-dialog -->
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
    function send(id) {      
       var msg = $('#message').val();
        $("#btnsend").attr("disabled", true); 
        $("#btnsend").html('<i class="fa fa-spin fa-refresh"></i> Send...');
       $.ajax({
          url: "{{ url('/sendmsgvpltransfer') }}_" + id, 
          type: "get",  
          data: "msg=" + msg,              
           success: function(data) {                       
               $("#msg_load").load(location.href + ' #msg_load')   
           }           
       });    
   }
</script> 

<script>
    $("#message").on("keypress", function(event) {                
        var id = "{{ $vpltransfer->id }}";
        if(event.which == 13){            
            send(id);                      
            location.reload();
        }
        
    });  
</script>
<script type="text/javascript"> 
    function closeWindow() {
            // window.close();
            setTimeout(function() {
                window.close(); 
        }, 300);
        }
</script>
@endsection