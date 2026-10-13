{{-- One free-text checklist row (PSM / OLA create + edit). $key is only a form index. --}}
<tr class="custom-doc-row border-t border-slate-100 dark:border-white/[0.06]">
    <td class="px-4 py-3">
        @if ($id)
            <input type="hidden" name="custom_documents[{{ $key }}][id]" value="{{ $id }}">
        @endif
        <input type="hidden" name="custom_documents[{{ $key }}][received]" value="0">
        <input type="checkbox" class="h-4 w-4 rounded border-slate-300"
            name="custom_documents[{{ $key }}][received]" value="1" @checked($received)>
    </td>
    <td class="px-4 py-3">
        <input type="text" class="agr-input custom-doc-name" style="height:40px;" maxlength="255"
            name="custom_documents[{{ $key }}][name]" placeholder="Document name" value="{{ $name }}">
    </td>
    <td class="px-4 py-3">
        <input type="text" class="agr-input doc-note" style="height:40px;" maxlength="2000"
            name="custom_documents[{{ $key }}][note]" placeholder="Notes" value="{{ $note }}">
    </td>
    <td class="px-1 py-3 text-center">
        <button type="button" class="btn-remove-custom-doc flex h-8 w-8 items-center justify-center rounded-full text-slate-400 hover:bg-red-50 hover:text-red-500" title="Remove">
            <i class="fa-solid fa-trash-can"></i>
        </button>
    </td>
</tr>
