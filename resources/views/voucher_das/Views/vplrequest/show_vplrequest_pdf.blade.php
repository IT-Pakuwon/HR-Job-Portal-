<table style="height: 53px; width: 681px;">
    <tbody>
        <tr style="height: 18px;">
            <td style="width: 406px; height: 18px;">{{ $parent }}</td>
            <td style="width: 26.1px; height: 18px;">DATE</td>
            <td style="width: 117.9px; height: 18px;">: {{ $created_at }}</td>
        </tr>
        <tr style="height: 18.5px;">
            <td style="width: 406px; height: 18.5px;">{{ $project }}</td>
            <td style="width: 26.1px; height: 18.5px;">NO</td>
            <td style="width: 117.9px; height: 18.5px;">: {{ $docid }}</td>
        </tr>
    </tbody>
</table>
<table style="height: 58px; width: 684px;">
    <tbody>
    <tr>
        <td style="width: 674px; text-align: center;">
            <h3><strong>{{ strtoupper($requesttype) }} VOUCHER/PRODUCT</strong></h3>
        </td>
    </tr>   
    </tbody>
</table>
    <table style="height: 101px; width: 677px;">
    <tbody>        
        <tr style="height: 21px;">
            <td style="width: 160.25px; height: 21px;">Type Transfer</td>
            <td style="width: 500.75px; height: 21px;">: {{ $requesttype }}</td>
        </tr>            
        <tr style="height: 25px;">
            <td style="width: 160.25px; height: 25px;">Remark</td>
            <td style="width: 500.75px; height: 25px;">: {{ $request_remark }}</td>
        </tr>
    </tbody>
</table>  
{{-- <p>&nbsp;</p> --}}
{{-- <table style="height: 21px; width: 676px;"> --}}
<table border="1" cellpadding="1" cellspacing="1" style="width:720px; border-collapse: collapse; border: 1px solid black;">
    <tbody>
        <tr style="height: 21px;">
            <td style="width: 80px; height: 21px;">ProductID</td>
            <td style="width: 100px; height: 21px;">Expired Date</td>
            <td style="width: 150px; height: 21px;">Name</td>          
            <td style="width: 30px; height: 21px;">Qty</td>
            <td style="width: 100px; height: 21px;">Gudang</td>           
        </tr>
        @foreach($vplrequestdetail as $dt)    
        <tr style="height: 26px;">
            <td style="width: 80px; height: 26px;">{{ $dt->product_id }}</td>
            <td style="width: 100px; height: 26px; text-align: center;">
                {{ $dt->expired_date == '1900-01-01' ? 'No Expired' : $dt->expired_date }}
            </td>
            <td style="width: 150px; height: 26px;">{{ $dt->product_name }}</td>           
            <td style="width: 30px; height: 26px;">{{ $dt->qty_request }}</td>
            <td style="width: 100px; height: 26px;">{{ $dt->whs_id }}</td>
           
        </tr>
        @endforeach    
    </tbody>
</table>
    <p>&nbsp;</p>
 
@if($approve_count >= 5)   
    <p>&nbsp;</p>
    <p>&nbsp;</p>
    <p>&nbsp;</p>
@endif
<table border="0" cellpadding="1" cellspacing="1" style="width:720px; border-collapse: collapse; border: 1px solid black;">
	<tbody>
		<tr>
			<td style="width:144px">Created By</td>
			<?php
			for ($i = 1; $i <= $approve_count; $i++) {					
                    if ($i == 1) {
                        echo '<td style="width: 25%;">Approved By</td>';
                    } else {
                        echo '<td style="width: 25%;">Approved By</td>';
                    }
				}
			?>  
		</tr>
		<tr>            
			<td>{{ $user }}
				<tag>
					<p style="color:blue">
					Created
					</p>
				</tag>
				<p>{{$req_date}}</p>
			</td>
			@foreach($t_approval as $dt2)
				<td>{{ $dt2->name }} 
					<tag>
					@if($dt2->status == 'A')
						<p style="color:blue">Approved</p>
					@elseif($dt2->status == 'R')
						<p style="color:red">Rejected</p>
					@elseif($dt2->status == 'P')
						<p style="color:red">Waiting</p>                                    
					@else
						<p style="color:red">Revised</p>
					@endif
					</tag>
					<p>{{$dt2->aprvdateafter}}</p>
				</td>
				
			@endforeach     </td>
			
		</tr>
		
	</tbody>
</table>