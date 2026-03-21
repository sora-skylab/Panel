<?php

namespace Pterodactyl\Tests\Unit\Services\Helpers;

use Illuminate\Support\Carbon;
use Pterodactyl\Tests\TestCase;
use Pterodactyl\Services\Helpers\FooterContentService;

class FooterContentServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-03-22 00:00:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function testItRendersSafeMarkupAndReplacesTheCurrentYearPlaceholder(): void
    {
        $service = new FooterContentService();

        $output = $service->render('&copy; 2022-{{current_year}} <strong>SKYLAB</strong>');

        $this->assertSame("\u{00A9} 2022-2026 <strong>SKYLAB</strong>", $output);
    }

    public function testItStripsUnsafeMarkupAndJavascriptUrls(): void
    {
        $service = new FooterContentService();

        $output = $service->render('<script>alert(1)</script><a href="javascript:alert(1)">bad</a><a href="https://example.com" target="_blank">ok</a>');

        $this->assertStringNotContainsString('<script', $output);
        $this->assertStringNotContainsString('javascript:', $output);
        $this->assertStringContainsString('alert(1)', $output);
        $this->assertStringContainsString('bad', $output);
        $this->assertStringContainsString('<a href="https://example.com" target="_blank" rel="noopener noreferrer nofollow">ok</a>', $output);
    }
}
