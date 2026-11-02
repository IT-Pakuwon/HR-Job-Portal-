@php($noteText=trim(strip_tags(str_ireplace(['<br>','<br/>','<br />'],"\n",$note??''))))
@if($noteText!=='')<button type="button" class="vms-note-hint" data-note-hint="{{ $noteText }}" aria-label="Catatan: {{ $noteText }}">?</button>@else<span aria-label="Tidak ada catatan">-</span>@endif
