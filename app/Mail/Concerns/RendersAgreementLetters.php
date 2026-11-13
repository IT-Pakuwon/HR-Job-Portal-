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
     * Data for the Surat 1 / Surat 2 email body of a PSM (Mall) or OLA (Office), and for the Surat 1 email of an
     * Addendum ($withAddendum). 'docLabel' is null for anything else (the generic types, and an Addendum's Surat 2 /
     * escalation, keep the reminder wording), and then nothing else is set.
     */
    protected function psmOlaEmailData(TrAgreement $agreement, bool $withAddendum = false): array
    {
        $kind = AgreementLetterService::kind($agreement);

        $docLabel = match (true) {
            $kind === null => null,
            in_array($agreement->agreement_type, ['ADDENDUM', 'FU_ADDENDUM'], true) => $withAddendum ? 'Addendum' : null,
            default => $kind === 'mall' ? 'PSM' : 'OLA',
        };

        if (! $docLabel) {
            return ['docLabel' => null];
        }

        $letters = app(AgreementLetterService::class);

        return [
            'docLabel' => $docLabel,
            'profile' => $letters->profile($agreement),
            'docs' => $letters->documents($agreement),
            'unitText' => $kind === 'mall' ? $letters->mallUnitText($agreement) : $letters->unitText($agreement),
            'company' => $this->resolveCompany($agreement),
        ];
    }

    /**
     * OFFICE (OLA) and MALL (PSM) agreements use their numbered letter; every other
     * type keeps the generic surat1 / surat2 templates below.
     */
    protected function renderNumberedLetterPdf(int $kind, TrAgreement $agreement, $sentDate, $surat1SentDate = null): string
    {
        return \PDF::loadView(...$this->numberedLetterView($kind, $agreement, $sentDate, $surat1SentDate))
            ->setPaper('a4', 'portrait')
            ->output();
    }

    /**
     * Surat 1 and Surat 2 of a PSM / OLA as one PDF (the escalation's "1 berkas Surat 1 & 2"):
     * Surat 2's pages follow Surat 1's, each keeping its own styles.
     */
    protected function renderSurat1And2Pdf(TrAgreement $agreement, $surat1SentDate, $surat2SentDate): string
    {
        $bodyOf = fn (string $html) => preg_match('~<body[^>]*>(.*)</body>~is', $html, $m) ? $m[1] : $html;
        $styleOf = fn (string $html) => preg_match('~<style[^>]*>(.*?)</style>~is', $html, $m) ? $m[1] : '';

        $one = view(...$this->numberedLetterView(1, $agreement, $surat1SentDate))->render();
        $two = view(...$this->numberedLetterView(2, $agreement, $surat2SentDate, $surat1SentDate))->render();

        // Both letters share the same template family; the second is wrapped so the page break lands between them.
        $html = '<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8"><style>'.$styleOf($one).'</style></head><body>'
            .$bodyOf($one)
            .'<div style="page-break-before:always;">'.$bodyOf($two).'</div></body></html>';

        return \PDF::loadHTML($html)->setPaper('a4', 'portrait')->output();
    }

    /**
     * The escalation (Tindak Lanjut) letter to Marketing/Leasing for a PSM / OLA. It has no
     * letter number of its own, so nothing is allocated here.
     */
    protected function renderEscalationLetterPdf(TrAgreement $agreement, $sentDate): string
    {
        $data = $this->psmOlaEmailData($agreement, withAddendum: true);

        return \PDF::loadView('pages.legal-agreement.pdf.surat-escalation', $data + [
            'agreement' => $agreement,
            'sentDate' => $sentDate,
        ])
            ->setPaper('a4', 'portrait')
            ->output();
    }

    // [view, data] of the numbered Surat 1 / Surat 2 letter, so it can be rendered to a PDF or to HTML.
    protected function numberedLetterView(int $kind, TrAgreement $agreement, $sentDate, $surat1SentDate = null): array
    {
        $letters = app(AgreementLetterService::class);

        $surat1 = $letters->letter($agreement, AgreementLetterService::SRT1, $kind === 1 ? $sentDate : $surat1SentDate);
        $letter = $kind === 1 ? $surat1 : $letters->letter($agreement, AgreementLetterService::SRT2, $sentDate);

        return ['pages.legal-agreement.pdf.surat-letter', [
            'kind' => $kind,
            'agreement' => $agreement,
            'company' => $this->resolveCompany($agreement),
            'profile' => $letters->profile($agreement),
            'letter' => $letter,
            'surat1' => $surat1,
            'docs' => $letters->documents($agreement),
            'unitText' => AgreementLetterService::isMall($agreement) ? $letters->mallUnitText($agreement) : $letters->unitText($agreement),
        ]];
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
