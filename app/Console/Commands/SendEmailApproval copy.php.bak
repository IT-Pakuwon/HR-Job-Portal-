<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Vinkla\Hashids\Facades\Hashids;
use Carbon\Carbon;

use App\Models\TrApproval;
use App\Models\ViewtrPurch;
use App\Models\User;

class SendEmailApproval extends Command
{
    protected $signature = 'email:approval-pending';
    protected $description = 'Send email notification for pending approvals';

    public function handle()
    {
        $this->info('Start SendEmailApproval...');

        $approvals = TrApproval::query()
            ->where('status', 'P')
            ->whereNotNull('aprv_datebefore')
            ->get();

        if ($approvals->isEmpty()) {
            $this->info('No pending approvals found.');
            return self::SUCCESS;
        }

        $docIds = $approvals->pluck('refnbr')
            ->filter()
            ->map(fn ($v) => trim((string) $v))
            ->unique()
            ->values()
            ->all();

        $purchMap = ViewtrPurch::query()
            ->whereIn('docid', $docIds)
            ->get()
            ->keyBy(fn ($row) => trim((string) $row->docid));

        $sent = 0;
        $failed = 0;

        foreach ($approvals as $task) {
            try {
                $refnbr = trim((string) $task->refnbr);
                $purch  = $purchMap->get($refnbr);

                if (!$purch) {
                    $this->warn("ViewtrPurch not found for refnbr: {$refnbr}");
                    continue;
                }

                $eid = Hashids::encode($purch->id);
                $baseUrl = rtrim((string) $purch->url, '/');

                $docType = strtoupper(trim((string) ($task->aprv_doctype ?: $this->extractDocType($refnbr))));
                $docName = $this->resolveDocName($docType);

                $formattedDate = $task->aprv_datebefore
                    ? Carbon::parse($task->aprv_datebefore)->format('d-m-Y H:i')
                    : '-';

                $multiapp = array_map('trim', explode(',', (string) $task->aprv_username));
                $multiapp = array_filter($multiapp);

                if (empty($multiapp)) {
                    $this->warn("No approver username for refnbr: {$refnbr}");
                    continue;
                }

                $emailUsers = User::query()
                    ->whereIn('username', $multiapp)
                    ->where('status', 'A')
                    ->whereNotNull('notification_email')
                    ->where('notification_email', '<>', '')
                    ->get();

                if ($emailUsers->isEmpty()) {
                    $this->warn("No active email recipient for refnbr: {$refnbr}");
                    continue;
                }

                foreach ($emailUsers as $emailsit) {
                    $recipientName = $emailsit->name ?: $emailsit->username;

                    $data = [
                        'docname'   => $docName,
                        'docid'     => $refnbr,
                        'status'    => 'P',
                        'cpnyid'    => $task->aprv_cpnyid ?: ($purch->cpnyid ?? '-'),
                        'deptname'  => $task->aprv_departementid ?: ($purch->departementid ?? '-'),
                        'date'      => $formattedDate,
                        'name'      => $recipientName,
                        'createdby' => $task->created_by ?? ($purch->created_user ?? 'System'),
                        'info'      => $purch->infohd ?? '-',
                        'url'       => url($baseUrl . '/' . $eid),
                    ];

                    Mail::send('emails.sendapprovenew', $data, function ($message) use ($data, $emailsit) {
                        $message->to($emailsit->notification_email)
                            ->subject($data['docid'] . ' - ' . $data['docname'] . ' - Waiting Approval')
                            ->from('digitalserver@pakuwon.com', 'Pakuwon System');
                    });

                    $sent++;
                }
            } catch (\Throwable $e) {
                $failed++;
                $this->error("Failed refnbr {$task->refnbr}: " . $e->getMessage());
            }
        }

        $this->info("Finished. Sent: {$sent}, Failed: {$failed}");

        return self::SUCCESS;
    }

    private function extractDocType(string $docId): string
    {
        if (preg_match('/^[A-Z]+/', strtoupper($docId), $m)) {
            return $m[0];
        }

        return '';
    }

    private function resolveDocName(string $docType): string
    {
        $map = [
            'CS'   => 'Canvass Sheet',
            'PT'   => 'SPPT',
            'PJ'   => 'SPPJ',
            'PK'   => 'SPPK',
            'PB'   => 'SPPB',
            'RB'   => 'SPB',
            'GR'   => 'Receipt',
            'IS'   => 'Issue',
            'WO'   => 'Work Order',
            'BA' => 'BAST',
            'CA' => 'CALR',
        ];

        return $map[$docType] ?? ($docType !== '' ? $docType : 'Document');
    }
}