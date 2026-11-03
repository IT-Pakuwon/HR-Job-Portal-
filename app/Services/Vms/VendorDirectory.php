<?php

namespace App\Services\Vms;

use App\Models\Vms\Vendor;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class VendorDirectory
{
    public const TABS = ['new' => 'New Registration', 'incomplete' => 'Incomplete Registration Vendor', 'review' => 'Vendor Review', 'master' => 'Master Vendor'];

    public function query(string $tab, array $filters = []): Builder
    {
        abort_unless(isset(self::TABS[$tab]), 404);
        $query = Vendor::query()->from('vendor as v');
        if ($tab === 'new') {
            $query->where('v.account_status', 0)->where('v.type', '!=', 'manual');
            $query->leftJoin('vendor_category as b', 'b.category_id', '=', 'v.bidang_usaha');
        } else {
            $query->where('v.account_status', 1)->join('vendor_category as b', 'b.category_id', '=', 'v.bidang_usaha');
            if ($tab === 'master') {
                $query->where('v.data_status', 'approved')->where('v.step', 5);
                // CI3 excludes master vendors without basic information.
                $query->whereExists(fn ($q) => $q->selectRaw('1')->from('vendor_basic_information as i')->whereColumn('i.vendor_id', 'v.vendor_id'));
            } else {
                $query->whereIn('v.data_status', ['inprogress', 'revise'])->where('v.step', $tab === 'review' ? '>' : '<', $tab === 'review' ? 4 : 5);
            }
        }
        $query->leftJoin('vendor_category_parent as c', 'c.id', '=', 'v.vendor_category');
        if (! empty($filters['category'])) {
            $query->where('v.vendor_category', $filters['category']);
        }
        if (! empty($filters['business'])) {
            $query->where('v.bidang_usaha', $filters['business']);
        }
        if ($tab === 'master' && ! empty($filters['type'])) {
            $query->where('v.type', $filters['type']);
        }
        if ($tab === 'master' && ! empty($filters['tag'])) {
            $query->whereExists(fn ($q) => $q->selectRaw('1')->from('vendor_tag as t')->whereColumn('t.id_vendor', 'v.vendor_id')->where('t.id_tag', $filters['tag']));
        }

        return $query;
    }

    public function counts(): array
    {
        $counts = [];
        foreach (array_keys(self::TABS) as $tab) {
            $counts[$tab] = $this->query($tab)->count('v.vendor_id');
        }

        return $counts;
    }

    public function rows(string $tab, array $filters = [])
    {
        $query = $this->query($tab, $filters)->select('v.vendor_id', 'v.second_vendor_id', 'v.vendor_nama', 'v.username', 'v.email', 'v.company_profile', 'v.created_at', 'v.step', 'v.type', 'v.data_status', 'c.parent_category_name', 'b.category_name');
        if ($tab === 'review') {
            foreach (['procurement', 'legal', 'tax', 'finance'] as $role) {
                $query->addSelect('v.'.$role.'_approval');
            }

        }
        if ($tab === 'master') {
            $query->selectSub(fn ($q) => $q->from('vendor_basic_information')->select('phone1')->whereColumn('vendor_id', 'v.vendor_id')->orderByDesc('id')->limit(1), 'phone1');
        }
        $rows = $query->orderByDesc($tab === 'master' ? 'v.second_vendor_id' : 'v.vendor_id')->orderByDesc('v.vendor_id')->get();
        if ($tab === 'review') {
            $this->attachReviewCounts($rows);
        }
        foreach ($rows as $row) {
            $row->setAttribute('profile_url', $this->fileUrl('company_profile', $row->company_profile));
        }

        return $rows;
    }

    private function attachReviewCounts($rows): void
    {
        if ($rows->isEmpty()) {
            return;
        }
        // Match view_pendingdocumentdept/view_document without executing nested views.
        // Only aggregate documents for vendors returned by the active filters.
        $summary = DB::connection('mysql7')->table('vendor_document as d')
            ->join('master_document as m', 'm.document_id', '=', 'd.document_id')
            ->where('m.status', 1)->whereIntegerInRaw('d.vendor_id', $rows->pluck('vendor_id')->all())
            ->select('d.vendor_id');
        $expressions = [
            'proc_pending' => ['procurement', ['rejected', 'waiting_review', 'empty']],
            'legal_pending' => ['legal', ['rejected', 'waiting_review', 'empty']],
            'tax_rejected' => ['tax', ['rejected']],
            'tax_waitingreview' => ['tax', ['waiting_review']],
            'tax_request' => ['tax', ['empty']],
            'treasury_rejected' => ['finance', ['rejected']],
            'treasury_waitingreview' => ['finance', ['waiting_review']],
            'treasury_request' => ['finance', ['empty']],
        ];
        foreach ($expressions as $alias => [$department,$statuses]) {
            $placeholders = implode(',', array_fill(0, count($statuses), '?'));
            $summary->selectRaw('SUM(CASE WHEN m.devision_id = ? AND d.status IN ('.$placeholders.') THEN 1 ELSE 0 END) AS '.$alias, [$department, ...$statuses]);
        }
        $counts = $summary->groupBy('d.vendor_id')->get()->keyBy('vendor_id');
        foreach ($rows as $row) {
            $count = $counts->get($row->vendor_id);
            foreach (array_keys($expressions) as $field) {
                $row->setAttribute($field, $count ? (int) $count->{$field} : null);
            }
        }
    }

    public function fileUrl(string $folder, ?string $filename): ?string
    {
        if (! $filename || str_contains($filename, '..') || str_contains($filename, '/') || str_contains($filename, '\\')) {
            return null;
        }

        return rtrim(config('vms.attachments_url'), '/').'/'.$folder.'/'.rawurlencode($filename);
    }

    public function incomplete(int $id): array
    {
        abort_unless($this->query('incomplete')->where('v.vendor_id', $id)->exists(), 409, 'Vendor ini tidak lagi berada di tahap Incomplete Registration. Muat ulang daftar.');

        return $this->information($id);
    }

    public function information(int $id): array
    {
        $data = $this->detail($id);
        $db = DB::connection('mysql7');
        $data['basic'] = $db->table('vendor_basic_information as b')->leftJoin('badan_usaha as u', 'u.BadanID', '=', 'b.badan_usaha')->where('b.vendor_id', $id)->orderByDesc('b.id')->first(['b.company_name', 'b.attention', 'b.email', 'b.email2', 'b.website', 'b.phone1', 'b.phone2', 'b.fax', 'b.address1', 'b.address2', 'b.country', 'b.state', 'b.city', 'b.postal_code', 'b.tanggal_berdiri', 'u.Description as badan_usaha_name']);
        $fields = ['id', 'vendor_id', 'status_npwp', 'rejection_note', 'npwp_name', 'npwp_number', 'npwp_address'];
        foreach (['shipper', 'payment'] as $prefix) {
            foreach (['company_name', 'attention', 'email', 'website', 'phone1', 'phone2', 'fax', 'address1', 'address2', 'city', 'country', 'state', 'postal_code'] as $field) {
                $fields[] = $prefix.'_'.$field;
            }
        }
        $data['npwp'] = $db->table('vendor_npwp')->where('vendor_id', $id)->orderByDesc('id')->first($fields);
        $business = $db->table('vendor')->where('vendor_id', $id)->value('bidang_usaha');
        $requirements = $db->table('setup_document as s')->join('master_document as m', 'm.document_id', '=', 's.document_id')->where('s.category_id', $business)->where('s.is_published', 1)->where('m.status', 1)->orderBy('m.document_order')->get(['m.document_id', 'm.document_name', 'm.document_remark', 'm.has_conditional', 'm.conditional_date', 's.is_mandatory'])->unique('document_id')->values();
        $uploaded = $data['documents']->groupBy('document_id');
        foreach ($requirements as $document) {
            $established = $data['basic']->tanggal_berdiri ?? null;
            $cutoff = $document->conditional_date && $document->conditional_date !== '0000-00-00' ? $document->conditional_date : '2007-08-15';
            $document->mandatory = (int) $document->is_mandatory === 1 && ! ($document->has_conditional === 'Y' && $established && $established > $cutoff);
            $document->uploads = $uploaded->get($document->document_id, collect());
        }
        $data['requirements'] = $requirements;
        $data['additionalDocuments'] = $data['documents']->whereNotIn('document_id', $requirements->pluck('document_id'));

        return $data;
    }

    public function detail(int $id): array
    {
        $vendor = Vendor::query()->leftJoin('vendor_category as b', 'b.category_id', '=', 'vendor.bidang_usaha')->leftJoin('vendor_category_parent as c', 'c.id', '=', 'vendor.vendor_category')
            ->select('vendor.vendor_id', 'vendor.second_vendor_id', 'vendor.vendor_nama', 'vendor.username', 'vendor.email', 'vendor.email2', 'vendor.company_profile', 'vendor.npwp', 'vendor.account_status', 'vendor.step', 'vendor.data_status', 'vendor.type', 'vendor.created_at', 'vendor.approved_date', 'vendor.status', 'b.category_name', 'c.parent_category_name')
            ->findOrFail($id);
        $approval = Vendor::whereKey($id)->first(['procurement_approval', 'procurement_notes', 'procurement_date', 'legal_approval', 'legal_notes', 'legal_date', 'tax_approval', 'tax_notes', 'tax_date', 'finance_approval', 'finance_notes', 'finance_date']);
        $db = DB::connection('mysql7');
        $basic = $db->table('vendor_basic_information')->where('vendor_id', $id)->orderByDesc('id')->first(['company_name', 'attention', 'email', 'phone1', 'phone2', 'website', 'address1', 'address2', 'city', 'state', 'country', 'postal_code', 'tanggal_berdiri']);
        $npwp = $db->table('vendor_npwp')->where('vendor_id', $id)->orderByDesc('id')->first(['npwp_name', 'npwp_number', 'npwp_address', 'status_npwp', 'rejection_note']);
        $banks = $db->table('vendor_bank as a')->leftJoin('master_bank as b', 'b.bank_id', '=', 'a.bank_code')->where('a.vendor_id', $id)->get(['a.id', 'a.bank_country', 'a.bank_code', 'a.bank_account_number', 'a.bank_account_name', 'a.status', 'a.rejection_note', 'b.bank_name']);
        $documents = $db->table('vendor_document as a')->leftJoin('master_document as d', 'd.document_id', '=', 'a.document_id')->where('a.vendor_id', $id)->orderBy('a.id')->get(['a.id', 'a.document_id', 'd.document_name', 'd.devision_id', 'a.doc_name', 'a.doc_expire', 'a.created_at', 'a.status', 'a.notes', 'a.remark']);
        foreach ($documents as $document) {
            $document->file_url = $this->fileUrl('doc/'.$id, $document->doc_name);
        }
        $history = $db->table('vendor_internal_activity as a')->leftJoin('admin as u', 'u.id', '=', 'a.created_by')->where('a.vendor_id', $id)->orderByDesc('a.id')->get(['a.activity', 'a.notes', 'a.created_at', 'u.name', 'u.username']);

        return compact('vendor', 'approval', 'basic', 'npwp', 'banks', 'documents', 'history') + ['profileUrl' => $this->fileUrl('company_profile', $vendor->company_profile)];
    }
}
