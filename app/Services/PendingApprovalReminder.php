<?php

namespace App\Services;

use App\Models\TrApproval;
use App\Models\User;
use App\Models\ViewtrPurch;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;
use Vinkla\Hashids\Facades\Hashids;

class PendingApprovalReminder
{
    public function pendingQuery(string $username)
    {
        return TrApproval::where('status', 'P')->whereNotNull('aprv_datebefore')
            ->whereRaw("? = ANY(regexp_split_to_array(LOWER(BTRIM(aprv_username)), '\\s*[,;]\\s*'))", [mb_strtolower(trim($username))]);
    }

    public function summary(User $user): array
    {
        $approvals = $this->pendingQuery($user->username)->orderBy('aprv_datebefore')->orderBy('id')->get();
        $docIds = $approvals->pluck('refnbr')->filter()->map(fn ($id) => trim((string) $id))->unique()->values();
        $purchases = $docIds->isEmpty() ? collect() : ViewtrPurch::whereIn('docid', $docIds)->get();
        $documents = $this->documents($approvals, $purchases);

        return [
            'name' => $user->name ?: $user->username,
            'username' => $user->username,
            'total' => count($documents),
            'documents' => $documents,
            'status' => 'P',
            'skipped_count' => $approvals->count() - count($documents),
        ];
    }

    public function documents(Collection $approvals, Collection $purchases): array
    {
        $byDocument = $purchases->groupBy(fn ($row) => trim((string) $row->docid));
        $names = ['CS' => 'Canvass Sheet', 'PT' => 'SPPT', 'PJ' => 'SPPJ', 'PK' => 'SPPK', 'PB' => 'SPPB',
            'RB' => 'SPB', 'GR' => 'Receipt', 'IS' => 'Issue', 'WO' => 'Work Order', 'BA' => 'BAST', 'CA' => 'CALR', 'VCR' => 'Voucher Taxi'];
        $documents = [];
        foreach ($approvals as $task) {
            $refnbr = trim((string) $task->refnbr);
            $matches = $byDocument->get($refnbr, collect());
            if (!empty($task->aprv_cpnyid)) {
                $matches = $matches->filter(fn ($row) => trim((string) ($row->cpnyid ?? '')) === trim((string) $task->aprv_cpnyid));
            }
            $purch = $matches->count() === 1 ? $matches->first() : null;
            if (!$purch) continue;
            preg_match('/^[A-Z]+/', strtoupper($refnbr), $prefix);
            $type = strtoupper(trim((string) ($task->aprv_doctype ?: ($prefix[0] ?? ''))));
            $documents[] = [
                'docname' => $names[$type] ?? ($type ?: 'Document'),
                'docid' => $refnbr,
                'cpnyid' => $task->aprv_cpnyid ?: ($purch->cpnyid ?? '-'),
                'deptname' => $task->aprv_departementid ?: ($purch->departementid ?? '-'),
                'date' => Carbon::parse($task->aprv_datebefore)->format('d-m-Y H:i'),
                'createdby' => $task->created_by ?? ($purch->created_user ?? 'System'),
                'info' => $purch->infohd ?? '-',
                'url' => url(rtrim((string) $purch->url, '/').'/'.Hashids::encode($purch->id)),
            ];
        }
        return $documents;
    }

    public function send(User $user, array $summary): void
    {
        Mail::send('emails.sendapprovenew', $summary, function ($message) use ($summary, $user) {
            $message->to(trim($user->notification_email))
                ->subject('Approval Pending Summary - Total '.$summary['total'].' Document(s)')
                ->from(config('mail.from.address'), config('app.name'));
        });
    }
}
