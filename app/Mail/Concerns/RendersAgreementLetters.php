<?php

namespace App\Mail\Concerns;

use App\Models\MsCompany;
use App\Models\TrAgreement;
use App\Models\TrAgreementAttachment;
use Google\Cloud\Storage\StorageClient;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Shared PDF/attachment plumbing for the Surat 1 / Surat 2 reminder letters,
 * so both Mailables render letters and fetch tr_agreement_attachment files
 * the same way instead of duplicating the GCS download logic.
 */
trait RendersAgreementLetters
{
    protected function resolveCompany(TrAgreement $agreement): ?MsCompany
    {
        return MsCompany::query()
            ->where('cpny_id', $agreement->cpny_id)
            ->first();
    }

    protected function renderSurat1Pdf(TrAgreement $agreement, $sentDate): string
    {
        return \PDF::loadView('pages.legal-agreement.pdf.surat1', [
            'agreement' => $agreement,
            'company' => $this->resolveCompany($agreement),
            'sentDate' => $sentDate,
        ])
            ->setPaper('a4', 'portrait')
            ->output();
    }

    protected function renderSurat2Pdf(TrAgreement $agreement, $sentDate, $surat1SentDate): string
    {
        return \PDF::loadView('pages.legal-agreement.pdf.surat2', [
            'agreement' => $agreement,
            'company' => $this->resolveCompany($agreement),
            'sentDate' => $sentDate,
            'surat1SentDate' => $surat1SentDate,
        ])
            ->setPaper('a4', 'portrait')
            ->output();
    }

    /**
     * Downloads the raw bytes of an agreement attachment (tr_agreement_attachment,
     * stored on GCS) so it can be attached to an outgoing letter email.
     */
    protected function downloadAgreementAttachmentFile(TrAgreementAttachment $row): ?array
    {
        try {
            $config = config('filesystems.disks.gcs');

            $keyFilePath = $config['key_file'];

            if (!Str::startsWith($keyFilePath, ['/', 'C:\\', 'D:\\'])) {
                $keyFilePath = base_path($keyFilePath);
            }

            $storage = new StorageClient([
                'projectId' => $config['project_id'],
                'keyFilePath' => $keyFilePath,
            ]);

            $bucket = $storage->bucket($config['bucket']);

            $objectPath = rtrim($row->folder, '/').'/'.$row->filename;

            $content = $bucket->object($objectPath)->downloadAsString();

            $mimeMap = [
                'pdf' => 'application/pdf',
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'png' => 'image/png',
            ];

            $ext = strtolower((string) $row->extention);

            return [
                'content' => $content,
                'ext' => $ext,
                'mime' => $mimeMap[$ext] ?? 'application/octet-stream',
            ];
        } catch (\Throwable $e) {
            Log::warning('Agreement attachment download failed', [
                'attachment_id' => $row->id,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Latest agreement attachment matching a label prefix, scoped to the
     * agreement's current revision (renewal_sequence) — used to find the
     * "Bukti Pengiriman" (Tanda Terima) proof of delivery.
     */
    protected function findLatestAttachmentByLabel(TrAgreement $agreement, string $labelPrefix): ?TrAgreementAttachment
    {
        return TrAgreementAttachment::query()
            ->where('agreement_id', $agreement->agreement_id)
            ->where('renewal_sequence', $agreement->renewal_sequence)
            ->where('status', 'A')
            ->where('attachment_name', 'like', $labelPrefix.'%')
            ->orderByDesc('id')
            ->first();
    }
}
