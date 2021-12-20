<?php

namespace Tests\Unit\Services;

use App\Services\HelperService;
use PHPUnit\Framework\TestCase;

class HelperServiceTest extends TestCase
{
    private HelperService $service;

    protected function setUp(): void
    {
        $this->service = new HelperService();
    }

    /** @test */
    public function it_will_bring_back_empty_code_when_no_format_string_is_given()
    {
        $code = $this->service->generateCode();

        $this->assertEmpty($code);
    }
}
