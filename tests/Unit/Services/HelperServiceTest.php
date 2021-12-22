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
    public function it_will_return_true_when_all_characters_in_string_are_uppercase_alpha_characters()
    {
        foreach (str_split(HelperService::UPPER_CASE_ALPHA_CHARACTERS) as $char) {
            $this->assertTrue($this->service->isUpperAlphaCharacter($char));
        }
    }

    /** @test */
    public function it_will_return_false_when_all_character_is_not_uppercase_alpha_character()
    {
        foreach (str_split(HelperService::LOWER_CASE_ALPHA_CHARACTERS) as $char) {
            $this->assertFalse($this->service->isUpperAlphaCharacter($char));
        }
    }

    /** @test */
    public function it_will_return_true_when_all_characters_in_string_are_lowercase_alpha_characters()
    {
        foreach (str_split(HelperService::LOWER_CASE_ALPHA_CHARACTERS) as $char) {
            $this->assertTrue($this->service->isLowerAlphaCharacter($char));
        }
    }

    /** @test */
    public function it_will_return_false_when_all_characters_in_string_are_uppercase_alpha_characters()
    {
        foreach (str_split(HelperService::UPPER_CASE_ALPHA_CHARACTERS) as $char) {
            $this->assertFalse($this->service->isLowerAlphaCharacter($char));
        }
    }

    /** @test */
    public function it_will_bring_back_empty_string_code_when_no_format_string_is_given()
    {
        $code = $this->service->generateCode();

        $this->assertIsString($code);
        $this->assertEmpty($code);
    }

    /** @test */
    public function it_will_bring_back_code_in_digit_format()
    {
        $codeFormat = "####";
        $code = $this->service->generateCode("####");

        $this->assertEquals(strlen($codeFormat), strlen($code));
        foreach (str_split($code) as $codeChar) {
            $this->assertIsNumeric($codeChar);
        }
    }

    /** @test */
    public function it_will_bring_back_code_in_alpha_character_format()
    {
        $codeFormat = "????";
        $code = $this->service->generateCode($codeFormat);

        $this->assertEquals(strlen($codeFormat), strlen($code));
        foreach (str_split($code) as $codeChar) {
            $this->assertTrue($this->service->isUpperAlphaCharacter($codeChar));
        }
    }

    /** @test */
    public function it_will_bring_back_code_in_both_alpha_digit_character_format()
    {
        $codeFormat = "#?#?";
        $code = $this->service->generateCode($codeFormat);

        $this->assertEquals(strlen($codeFormat), strlen($code));
        $this->assertIsNumeric($code[0]);
        $this->assertTrue($this->service->isUpperAlphaCharacter($code[1]));
        $this->assertIsNumeric($code[2]);
        $this->assertTrue($this->service->isUpperAlphaCharacter($code[3]));
    }

    /** @test */
    public function it_will_generate_two_different_codes()
    {
        $codeFormat = "#?#?";
        $firstCode = $this->service->generateCode($codeFormat);
        $secondCode = $this->service->generateCode($codeFormat);

        $this->assertNotEquals($firstCode, $secondCode);
    }
}
