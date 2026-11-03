<?php

namespace Tests\Unit;

use App\Services\ZoomApi;
use App\Services\ZoomRecordings;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Illuminate\Cache\ArrayStore;
use Illuminate\Cache\Repository;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\Facade;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

class ZoomRecordingsTest extends TestCase
{
    private $previousApplication;

    protected function setUp(): void
    {
        parent::setUp();
        $this->previousApplication = Facade::getFacadeApplication();
        $container = new Container();
        $container->instance('cache', new Repository(new ArrayStore()));
        $container->instance('log', new NullLogger());
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication($container);
    }

    protected function tearDown(): void
    {
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication($this->previousApplication);
        parent::tearDown();
    }

    private function file(array $overrides = []): array
    {
        return array_replace(['status' => 'completed', 'file_type' => 'MP4', 'play_url' => 'https://zoom.us/rec/play/example'], $overrides);
    }

    public function test_button_requires_a_completed_playable_recording(): void
    {
        $api = $this->createMock(ZoomApi::class);
        $service = new ZoomRecordings($api);
        $invalid = [
            $this->file(['status' => 'processing']),
            $this->file(['play_url' => '']),
            $this->file(['file_type' => 'TRANSCRIPT']),
            $this->file(['play_url' => 'javascript:alert(1)']),
        ];
        $this->assertSame([], $service->playableFiles(['recording_files' => $invalid]));
        $this->assertCount(2, $service->playableFiles(['recording_files' => [...$invalid, $this->file(), $this->file(['file_type' => 'M4A'])]]));
        $api->expects($this->once())->method('getRecordings')->with('123', 'business')
            ->willReturn(['recording_files' => [$this->file()]]);
        $this->assertTrue($service->hasPlayable('123', 'business', true));
        $this->assertTrue($service->hasPlayable('123', 'business', true));
    }

    public function test_upcoming_and_unlinked_meetings_do_not_call_zoom(): void
    {
        $api = $this->createMock(ZoomApi::class);
        $api->expects($this->never())->method('getRecordings');
        $service = new ZoomRecordings($api);
        $this->assertFalse($service->hasPlayable('123', null, false));
        $this->assertFalse($service->hasPlayable(null, null, true));
    }

    public function test_absent_recordings_are_hidden_and_cached(): void
    {
        $api = $this->createMock(ZoomApi::class);
        $api->expects($this->once())->method('getRecordings')->willThrowException(
            new ClientException('Not found', new Request('GET', 'https://zoom.us'), new Response(404))
        );
        $service = new ZoomRecordings($api);
        $this->assertFalse($service->hasPlayable('123', null, true));
        $this->assertSame([], $service->get('123'));
    }

    public function test_cache_is_separate_for_each_zoom_account(): void
    {
        $api = $this->createMock(ZoomApi::class);
        $api->expects($this->exactly(2))->method('getRecordings')->willReturnCallback(
            fn ($id, $account) => ['recording_files' => $account === 'business' ? [$this->file()] : []]
        );
        $service = new ZoomRecordings($api);
        $this->assertTrue($service->hasPlayable('123', 'business', true));
        $this->assertFalse($service->hasPlayable('123', 'other', true));
    }

    public function test_zoom_failure_hides_button_without_breaking_meeting_list(): void
    {
        $api = $this->createMock(ZoomApi::class);
        $api->method('getRecordings')->willThrowException(new \RuntimeException('Unavailable'));
        $this->assertFalse((new ZoomRecordings($api))->hasPlayable('123', 'business', true));
    }
}
