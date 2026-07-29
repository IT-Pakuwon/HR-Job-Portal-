<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Vinkla\Hashids\Facades\Hashids;
use Carbon\Carbon;

use App\Models\TrApproval;
use App\Models\ViewtrPurch;
use App\Models\User;

class SendEmailApprovalDeclined extends Command
{
    protected $signature = 'email:approval-declined';
    protected $description = 'Send email notification for declined approvals';

    public function handle()
    {
        $this->info('Start SendEmailApprovalDeclined...');

        $approvals = TrApproval::query()
            ->where('status', 'D')
            ->whereNotNull('refnbr')
            ->get();

        if ($approvals->isEmpty()) {
            $this->info('No declined approvals found.');
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
            ->where('status', 'D')
            ->get()
            ->keyBy(fn ($row) => trim((string) $row->docid));

        $sent = 0;
        $failed = 0;

        foreach ($approvals as $task) {
            try {
                $refnbr = trim((string) $task->refnbr);
                $purch  = $purchMap->get($refnbr);

                if (!$purch) {
                    $this->warn("ViewtrPurch not found / not declined for refnbr: {$refnbr}");
                    continue;
                }

                $createdUser = trim((string) ($purch->created_user ?? ''));

                if ($createdUser === '') {
                    $this->warn("created_user kosong untuk refnbr: {$refnbr}");
                    continue;
                }

                $emailUser = User::query()
                    ->where('username', $createdUser)
                    ->where('status', 'A')
                    ->whereNotNull('notification_email')
                    ->where('notification_email', '<>', '')
                    ->first();

                if (!$emailUser) {
                    $this->warn("Email user tidak ditemukan untuk created_user: {$createdUser}, refnbr: {$refnbr}");
                    continue;
                }

                $eid = Hashids::encode($purch->id);
                $baseUrl = rtrim((string) $purch->url, '/');

                $docType = strtoupper(trim((string) ($task->aprv_doctype ?: $this->extractDocType($refnbr))));
                $docName = $this->resolveDocName($docType);

                $formattedDate = $task->updated_at
                    ? Carbon::parse($task->updated_at)->format('d-m-Y H:i')
                    : ($task->created_at ? Carbon::parse($task->created_at)->format('d-m-Y H:i') : '-');

                $recipientName = $emailUser->name ?: $emailUser->username;

                $data = [
                    'docname'   => $docName,
                    'docid'     => $refnbr,
                    'status'    => 'D',
                    'cpnyid'    => $task->aprv_cpnyid ?: ($purch->cpnyid ?? '-'),
                    'deptname'  => $task->aprv_departementid ?: ($purch->departementid ?? '-'),
                    'date'      => $formattedDate,
                    'name'      => $recipientName,
                    'createdby' => $createdUser,
                    'info'      => $purch->infohd ?? '-',
                    'url'       => url($baseUrl . '/' . $eid),
                ];

                Mail::send('emails.sendapprovenew', $data, function ($message) use ($data, $emailUser) {
                    $message->to($emailUser->notification_email)
                        ->subject($data['docid'] . ' - ' . $data['docname'] . ' - Declined')
                        ->from('digitalserver@pakuwon.com', 'Pakuwon System');
                });

                $sent++;
                $this->info("Email declined sent to {$emailUser->notification_email} for {$refnbr}");
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
        ];

        return $map[$docType] ?? ($docType !== '' ? $docType : 'Document');
    }
}