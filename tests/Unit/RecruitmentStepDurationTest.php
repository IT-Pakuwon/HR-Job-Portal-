<?php

namespace Tests\Unit;

use App\Services\RecruitmentStepDuration;
use PHPUnit\Framework\TestCase;

class RecruitmentStepDurationTest extends TestCase
{
    public function test_cumulative_days_run_from_workflow_creation_and_stop_at_decision(): void
    {
        $steps = collect([
            (object) ['step_id'=>'JOAPHC', 'step_order'=>1, 'created_at'=>'2026-11-02 09:00:00', 'aprvuserdate'=>'2026-11-03 09:00:00'],
            (object) ['step_id'=>'IU', 'step_order'=>6, 'created_at'=>'2026-11-02 09:00:00', 'aprvuserdate'=>null],
        ]);
        $running = RecruitmentStepDuration::cumulative($steps, 'IU', null, 'P', '2026-11-12 09:00:00');
        $this->assertSame(10, $running['days']);
        $this->assertSame('2026-11-02 09:00:00', $running['start']);
        $steps[1]->aprvuserdate = '2026-11-06 09:00:00';
        $rejected = RecruitmentStepDuration::cumulative($steps, 'IU', null, 'R', '2026-11-12 09:00:00');
        $this->assertSame(4, $rejected['days']);
        $this->assertSame('2026-11-06 09:00:00', $rejected['end']);
        $this->assertNull(RecruitmentStepDuration::cumulative(collect(), 'IU', null, 'P'));
    }

    public function test_step_durations_use_creation_previous_approval_and_schedule_creation(): void
    {
        $steps = collect([
            (object) ['id'=>1, 'step_id'=>'JOAPHC', 'step_order'=>1, 'status'=>'A', 'created_at'=>'2026-11-01 09:00:00', 'aprvuserdate'=>'2026-11-03 09:00:00'],
            (object) ['id'=>2, 'step_id'=>'JOAPUS', 'step_order'=>2, 'status'=>'A', 'created_at'=>null, 'aprvuserdate'=>'2026-11-06 09:00:00'],
            (object) ['id'=>3, 'step_id'=>'WIHC', 'step_order'=>3, 'status'=>'X', 'created_at'=>null, 'aprvuserdate'=>null],
            (object) ['id'=>4, 'step_id'=>'WIU', 'step_order'=>5, 'status'=>'A', 'created_at'=>null, 'aprvuserdate'=>'2026-11-11 17:00:00'],
            (object) ['id'=>5, 'step_id'=>'IU', 'step_order'=>6, 'status'=>'A', 'created_at'=>null, 'aprvuserdate'=>'2026-11-12 18:00:00'],
            (object) ['id'=>6, 'step_id'=>'JOIN', 'step_order'=>9, 'status'=>'P', 'created_at'=>null, 'aprvuserdate'=>null],
        ]);
        $agendas = collect([
            (object) ['reftype'=>'IU', 'status'=>'P', 'created_at'=>'2026-10-01 09:00:00'],
            (object) ['reftype'=>'IU', 'status'=>'X', 'created_at'=>'2026-11-07 09:00:00'],
            (object) ['reftype'=>'IHU', 'status'=>'P', 'created_at'=>'2026-11-09 17:00:00'],
            (object) ['reftype'=>'IU', 'status'=>'P', 'created_at'=>'2026-11-10 17:00:00'],
        ]);
        $result = RecruitmentStepDuration::calculate($steps, $agendas, null, '2026-11-01 00:00:00');
        $this->assertSame(2, $result[1]['days']);
        $this->assertSame(3, $result[2]['days']);
        $this->assertSame(2, $result[4]['days']);
        $this->assertSame('2026-11-09 17:00:00', $result[4]['start']);
        $this->assertSame(1, $result[5]['days']);
        $this->assertArrayNotHasKey(3, $result);
        $this->assertArrayNotHasKey(6, $result);
    }

    public function test_missing_dates_do_not_show_a_duration(): void
    {
        $step = (object) ['id'=>1, 'step_id'=>'WIU', 'step_order'=>5, 'status'=>'A', 'created_at'=>null, 'aprvuserdate'=>'2026-11-11 17:00:00'];
        $this->assertSame([], RecruitmentStepDuration::calculate(collect([$step]), collect(), null));
    }
}
