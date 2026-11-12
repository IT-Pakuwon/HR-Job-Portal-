<?php

namespace Tests\Unit;

use App\Http\Controllers\CsListController;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class CsListSearchTest extends TestCase
{
    private function row(array $overrides = []): object
    {
        return (object) array_replace([
            'csid' => 'CS26100547', 'sppbjktid' => 'PB26100169',
            'assigndate' => '2026-10-19 08:00:00', 'submitdate' => '2026-11-02 10:00:00',
            'days' => 10, 'doctype_limit' => 4, 'cpny_id' => 'PSA',
            'department_id' => 'WAREHOUSE ATK', 'created_by' => 'anastasiamalonda',
            'keperluan' => 'Pengadaan printer kantor', 'next_approval' => 'KRISTIAN',
            'days_waiting' => 2, 'status_label' => 'On Progress',
            'csnote' => 'HiddenNote', 'user_peminta' => 'HiddenRequester', 'csdate' => '2000-01-01',
        ], $overrides);
    }

    private function searchMatches(string $search, ?object $row = null): bool
    {
        $method = new ReflectionMethod(CsListController::class, 'matchesVisibleSearch');
        $method->setAccessible(true);
        return $method->invoke(new CsListController(), $row ?? $this->row(), $search);
    }

    public function test_all_visible_text_columns_are_searchable_case_insensitively(): void
    {
        foreach (['cs26100547', 'pb26100169', 'psa', 'warehouse atk', 'anastasiamalonda', 'printer kantor', 'kristian', 'on progress'] as $search) {
            $this->assertTrue($this->searchMatches($search), $search);
        }
    }

    public function test_dates_match_displayed_and_iso_formats(): void
    {
        foreach (['19/10/2026', '2/11/2026', '02/11/2026', '2026-11-02'] as $search) {
            $this->assertTrue($this->searchMatches($search), $search);
        }
    }

    public function test_process_days_and_waiting_days_are_searchable(): void
    {
        $this->assertTrue($this->searchMatches('10 / 4'));
        $this->assertTrue($this->searchMatches('10/4'));
        $this->assertTrue($this->searchMatches('23', $this->row(['days_waiting' => 23])));
        $this->assertTrue($this->searchMatches('0 / 4', $this->row(['days' => 0])));
    }

    public function test_multiple_terms_can_match_different_visible_columns(): void
    {
        $this->assertTrue($this->searchMatches('PSA Kristian printer'));
        $this->assertFalse($this->searchMatches('GPS Kristian printer'));
    }

    public function test_hidden_fields_do_not_create_unexplained_matches(): void
    {
        foreach (['HiddenNote', 'HiddenRequester', '2000-01-01'] as $search) {
            $this->assertFalse($this->searchMatches($search));
        }
    }

    public function test_status_labels_and_empty_approval_values_are_supported(): void
    {
        $row = $this->row(['status_label' => 'Completed', 'next_approval' => '', 'days_waiting' => null]);
        $this->assertTrue($this->searchMatches('Completed', $row));
        $this->assertTrue($this->searchMatches('-', $row));
        $this->assertFalse($this->searchMatches('Kristian', $row));
    }

    public function test_search_treats_sql_wildcards_as_literal_text(): void
    {
        $this->assertFalse($this->searchMatches('%'));
        $this->assertTrue($this->searchMatches('100%', $this->row(['keperluan' => 'Pembayaran 100%'])));
    }
}
