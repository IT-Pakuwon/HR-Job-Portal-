<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Vinkla\Hashids\Facades\Hashids;
use Carbon\Carbon;

use App\Models\TrApproval;
use App\Models\ViewtrPurch;
use App\Models\User;

class SendEmailApproval extends Command
{
    protected $signature = 'email:approval-pending';
    protected $description = 'Send summary email notification for pending approvals';

    public function handle()
    {
        $this->info('Start SendEmailApproval Summary...');

        $approvals = TrApproval::query()
            // ->where('aprv_doctype', 'WO')
            // ->where('aprv_cpnyid', 'PSA')
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

        $summaryByApprover = [];

        foreach ($approvals as $task) {
            $refnbr = trim((string) $task->refnbr);
            $purch  = $purchMap->get($refnbr);

            if (!$purch) {
                $this->warn("ViewtrPurch not found for refnbr: {$refnbr}");
                continue;
            }

            $multiapp = array_map('trim', explode(',', (string) $task->aprv_username));
            $multiapp = array_filter($multiapp);

            if (empty($multiapp)) {
                $this->warn("No approver username for refnbr: {$refnbr}");
                continue;
            }

            $eid     = Hashids::encode($purch->id);
            $baseUrl = rtrim((string) $purch->url, '/');

            $docType = strtoupper(trim((string) ($task->aprv_doctype ?: $this->extractDocType($refnbr))));
            $docName = $this->resolveDocName($docType);

            $formattedDate = $task->aprv_datebefore
                ? Carbon::parse($task->aprv_datebefore)->format('d-m-Y H:i')
                : '-';

            foreach ($multiapp as $username) {
                $summaryByApprover[$username][] = [
                    'docname'   => $docName,
                    'docid'     => $refnbr,
                    'cpnyid'    => $task->aprv_cpnyid ?: ($purch->cpnyid ?? '-'),
                    'deptname'  => $task->aprv_departementid ?: ($purch->departementid ?? '-'),
                    'date'      => $formattedDate,
                    'createdby' => $task->created_by ?? ($purch->created_user ?? 'System'),
                    'info'      => $purch->infohd ?? '-',
                    'url'       => url($baseUrl . '/' . $eid),
                ];
            }
        }

        if (empty($summaryByApprover)) {
            $this->info('No valid pending approval summary found.');
            return self::SUCCESS;
        }

        $users = User::query()
            ->whereIn('username', array_keys($summaryByApprover))
            ->where('status', 'A')
            ->whereNotNull('notification_email')
            ->where('notification_email', '<>', '')
            ->get()
            ->keyBy('username');

        $sent = 0;
        $failed = 0;

        foreach ($summaryByApprover as $username => $documents) {
            try {
                $user = $users->get($username);

                if (!$user) {
                    $this->warn("No active email recipient for username: {$username}");
                    continue;
                }

                $data = [
                    'name'       => $user->name ?: $user->username,
                    'username'   => $user->username,
                    'total'      => count($documents),
                    'documents'  => $documents,
                    'status'     => 'P',
                ];

                Mail::send('emails.sendapprovenew', $data, function ($message) use ($data, $user) {
                    $message->to($user->notification_email)
                        ->subject('Approval Pending Summary - Total ' . $data['total'] . ' Document(s)')
                        // ->from('digitalserver@pakuwon.com', 'Pakuwon System');
                        ->from(config('mail.from.address'), config('app.name'));
                });

                $sent++;
            } catch (\Throwable $e) {
                $failed++;
                $this->error("Failed send summary to {$username}: " . $e->getMessage());
            }
        }

        $this->info("Finished. Email Sent: {$sent}, Failed: {$failed}");

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
            'CS' => 'Canvass Sheet',
            'PT' => 'SPPT',
            'PJ' => 'SPPJ',
            'PK' => 'SPPK',
            'PB' => 'SPPB',
            'RB' => 'SPB',
            'GR' => 'Receipt',
            'IS' => 'Issue',
            'WO' => 'Work Order',
            'BA' => 'BAST',
            'CA' => 'CALR',
            'VCR' => 'Voucher Taxi',
        ];

        return $map[$docType] ?? ($docType !== '' ? $docType : 'Document');
    }
}