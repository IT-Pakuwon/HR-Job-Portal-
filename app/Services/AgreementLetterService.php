<?php

namespace App\Services;

use App\Models\TrAgreement;
use App\Models\TrAgreementLetter;
use Carbon\Carbon;
use Illuminate\Database\QueryException;

/**
 * Data behind the OFFICE (OLA) Surat 1 / Surat 2 letters: the running letter
 * number, the list of related PSM/OLA + Addendum documents, and the unit text.
 */
class AgreementLetterService
{
    public const SRT1 = 'SRT1';
    public const SRT2 = 'SRT2';

    protected const ORDINALS = [
        1 => 'Pertama', 2 => 'Kedua', 3 => 'Ketiga', 4 => 'Keempat', 5 => 'Kelima',
        6 => 'Keenam', 7 => 'Ketujuh', 8 => 'Kedelapan', 9 => 'Kesembilan', 10 => 'Kesepuluh',
    ];

    protected const ROMAN_MONTHS = [
        1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI',
        7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII',
    ];

    // Office (OLA) and Mall (PSM) agreements get their own numbered letter;
    // every other type keeps the generic one.
    public static function isOffice(TrAgreement $agreement): bool
    {
        return strtoupper(trim((string) $agreement->property_cd)) === 'OFF'
            || in_array($agreement->agreement_type, ['OLA', 'FU_OLA'], true);
    }

    public static function isMall(TrAgreement $agreement): bool
    {
        return strtoupper(trim((string) $agreement->property_cd)) === 'MALL'
            || in_array($agreement->agreement_type, ['PSM', 'FU_PSM'], true);
    }

    // 'office' | 'mall' | null (null = generic letter).
    public static function kind(TrAgreement $agreement): ?string
    {
        return match (true) {
            self::isOffice($agreement) => 'office',
            self::isMall($agreement) => 'mall',
            default => null,
        };
    }

    public function profile(TrAgreement $agreement): array
    {
        $all = config('agreement_letters.'.self::kind($agreement), []);

        return array_replace_recursive($all['default'] ?? [], $all[$agreement->cpny_id] ?? []);
    }

    /**
     * The letter issued for this agreement's current follow-up cycle, created
     * on first use. Idempotent: a retry, the Surat 2 email re-printing Surat 1,
     * and the escalation email re-printing Surat 2 all get the same number.
     */
    public function letter(TrAgreement $agreement, string $type, $sentAt): TrAgreementLetter
    {
        $order = (float) $agreement->agreement_step_order;

        $find = fn () => TrAgreementLetter::query()
            ->where('agreement_id', $agreement->agreement_id)
            ->where('agreement_step_order', $order)
            ->where('letter_type', $type)
            ->first();

        if ($existing = $find()) {
            return $existing;
        }

        $sentAt = Carbon::parse($sentAt);
        $profile = $this->profile($agreement);

        // The unique indexes make a concurrent allocation fail instead of
        // duplicating a number; just take the next one.
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $seq = max(
                (int) ($profile['seq_floor'] ?? 0),
                (int) TrAgreementLetter::query()
                    ->where('cpny_id', $agreement->cpny_id)
                    ->where('letter_year', $sentAt->year)
                    ->max('letter_seq')
            ) + 1;

            $segments = array_filter([
                'LGL-'.($profile['code'] ?: $agreement->cpny_id),
                $profile['property_code'] ?? null,
                self::ROMAN_MONTHS[$sentAt->month],
                $sentAt->year,
            ]);

            try {
                return TrAgreementLetter::create([
                    'agreement_id' => $agreement->agreement_id,
                    'agreement_step_order' => $order,
                    'letter_type' => $type,
                    'cpny_id' => $agreement->cpny_id,
                    'letter_year' => $sentAt->year,
                    'letter_seq' => $seq,
                    'letter_no' => $seq.'/'.implode('/', $segments),
                    'sent_at' => $sentAt,
                ]);
            } catch (QueryException $e) {
                if ($existing = $find()) {
                    return $existing;
                }

                if ($attempt === 4) {
                    throw $e;
                }
            }
        }

        throw new \RuntimeException('Could not allocate a letter number for '.$agreement->agreement_id);
    }

    /**
     * The PSM/OLA and its Addenda, in order, as the letter's numbered list.
     * From an Addendum, climbs to the PSM/OLA it was made from; from a
     * PSM/OLA, picks up the Addenda made from it. Cancelled ones are skipped.
     *
     * @return array<int, array{label: string, no: string, date: ?Carbon}>
     */
    public function documents(TrAgreement $agreement): array
    {
        $isAddendum = $agreement->agreement_type === 'ADDENDUM';

        $root = $isAddendum && $agreement->parent_agreement_id
            ? $this->liveAgreements()->where('agreement_id', $agreement->parent_agreement_id)->first()
            : ($isAddendum ? null : $agreement);

        $addenda = $root
            ? $this->liveAgreements()
                ->where('parent_agreement_id', $root->agreement_id)
                ->where('agreement_type', 'ADDENDUM')
                ->orderBy('id')
                ->get()
            : collect();

        // An Addendum with no parent record still lists itself.
        if ($isAddendum && ! $addenda->contains('agreement_id', $agreement->agreement_id)) {
            $addenda->push($agreement);
        }

        $docs = [];

        if ($root && ($root->agreement_id === $agreement->agreement_id || filled($root->no_psm_or_addendum))) {
            $docs[] = $this->documentRow('Perjanjian Sewa Menyewa', $root);
        }

        foreach ($addenda->values() as $i => $addendum) {
            if ($addendum->agreement_id !== $agreement->agreement_id && blank($addendum->no_psm_or_addendum)) {
                continue;
            }

            $n = $i + 1;
            $docs[] = $this->documentRow('Addendum '.(self::ORDINALS[$n] ?? 'Ke-'.$n), $addendum);
        }

        return $docs;
    }

    protected function liveAgreements()
    {
        return TrAgreement::query()
            ->where('status', '<>', 'X')
            ->whereNull('deleted_at');
    }

    protected function documentRow(string $label, TrAgreement $source): array
    {
        return [
            'label' => $label,
            'no' => filled($source->no_psm_or_addendum) ? $source->no_psm_or_addendum : '-',
            'date' => $source->psm_or_addendum_date ? Carbon::parse($source->psm_or_addendum_date) : null,
        ];
    }

    /**
     * "Lantai 15 unit E, F, G, H" from floor_id 15 + unit_id OTBOAC150000E-H.
     * A unit id that doesn't end in a letter range is printed as-is.
     */
    public function unitText(TrAgreement $agreement): string
    {
        $parts = [];

        $floor = ltrim((string) $agreement->floor_id, '0');

        if ($floor !== '') {
            $parts[] = 'Lantai '.$floor;
        }

        $unit = trim((string) $agreement->unit_id);

        if (preg_match('/([A-Z])-([A-Z])$/', $unit, $m) && $m[1] <= $m[2]) {
            $parts[] = 'unit '.implode(', ', range($m[1], $m[2]));
        } elseif ($unit !== '') {
            $parts[] = 'unit '.$unit;
        }

        return implode(' ', $parts);
    }

    /**
     * "Lantai 2, Unit 208" for a mall shop. The unit is only printed when it
     * looks like a real unit number; the raw lot codes the mall data carries
     * (e.g. MKK00001000001) mean nothing to a tenant, so they are left out.
     */
    public function mallUnitText(TrAgreement $agreement): string
    {
        $parts = [];

        $floor = ltrim((string) $agreement->floor_id, '0');

        if ($floor !== '') {
            $parts[] = 'Lantai '.$floor;
        }

        $unit = trim((string) $agreement->unit_id);

        if (preg_match('/^\d{1,4}[A-Za-z]?$/', $unit)) {
            $parts[] = 'Unit '.$unit;
        }

        return implode(', ', $parts);
    }
}
