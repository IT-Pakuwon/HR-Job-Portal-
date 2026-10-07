<?php

namespace App\Mail\Concerns;

use App\Models\MsCompany;
use App\Models\TrAgreement;
use App\Models\TrAgreementAttachment;
use App\Services\AgreementLetterService;
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

    /**
     * OFFICE (OLA) and MALL (PSM) agreements use their numbered letter; every other
     * type keeps the generic surat1 / surat2 templates below.
     */
    protected function renderNumberedLetterPdf(int $kind, TrAgreement $agreement, $sentDate, $surat1SentDate = null): string
    {
        $letters = app(AgreementLetterService::class);

        $surat1 = $letters->letter($agreement, AgreementLetterService::SRT1, $kind === 1 ? $sentDate : $surat1SentDate);
        $letter = $kind === 1 ? $surat1 : $letters->letter($agreement, AgreementLetterService::SRT2, $sentDate);

        return \PDF::loadView('pages.legal-agreement.pdf.surat-'.AgreementLetterService::kind($agreement), [
            'kind' => $kind,
            'agreement' => $agreement,
            'company' => $this->resolveCompany($agreement),
            'profile' => $letters->profile($agreement),
            'letter' => $letter,
            'surat1' => $surat1,
            'docs' => $letters->documents($agreement),
            'unitText' => AgreementLetterService::isMall($agreement) ? $letters->mallUnitText($agreement) : $letters->unitText($agreement),
            // Surat 1 gives 14 days, Surat 2 gives 7 (the escalation window).
            'deadline' => $letter->sent_at->copy()->addDays($kind === 1 ? 14 : 7),
            'periodText' => $kind === 1 ? '2 (dua) minggu' : '7 (tujuh) hari kalender',
        ])
            ->setPaper('a4', 'portrait')
            ->output();
    }

    protected function renderSurat1Pdf(TrAgreement $agreement, $sentDate): string
    {
        if (AgreementLetterService::kind($agreement)) {
            return $this->renderNumberedLetterPdf(1, $agreement, $sentDate);
        }

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
        if (AgreementLetterService::kind($agreement)) {
            return $this->renderNumberedLetterPdf(2, $agreement, $sentDate, $surat1SentDate);
        }

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
