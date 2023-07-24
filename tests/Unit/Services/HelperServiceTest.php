<?php

use App\Services\HelperService;

beforeEach(function () {
    $this->service = new HelperService();
});

it('will return true when all characters in string are uppercase alpha characters', function () {
    foreach (str_split(HelperService::UPPER_CASE_ALPHA_CHARACTERS) as $char) {
        expect($this->service->isUpperAlphaCharacter($char))->toBeTrue();
    }
});

it('will return false when all character is not uppercase alpha character', function () {
    foreach (str_split(HelperService::LOWER_CASE_ALPHA_CHARACTERS) as $char) {
        expect($this->service->isUpperAlphaCharacter($char))->toBeFalse();
    }
});

it('will return true when all characters in string are lowercase alpha characters', function () {
    foreach (str_split(HelperService::LOWER_CASE_ALPHA_CHARACTERS) as $char) {
        expect($this->service->isLowerAlphaCharacter($char))->toBeTrue();
    }
});

it('will return false when all characters in string are uppercase alpha characters', function () {
    foreach (str_split(HelperService::UPPER_CASE_ALPHA_CHARACTERS) as $char) {
        expect($this->service->isLowerAlphaCharacter($char))->toBeFalse();
    }
});

it('will bring back empty string code when no format string is given', function () {
    $code = $this->service->generateCode();

    expect($code)->toBeString();
    expect($code)->toBeEmpty();
});

it('will bring back code in digit format', function () {
    $codeFormat = "####";
    $code = $this->service->generateCode("####");

    expect(strlen($code))->toEqual(strlen($codeFormat));
    foreach (str_split($code) as $codeChar) {
        expect($codeChar)->toBeNumeric();
    }
});

it('will bring back code in alpha character format', function () {
    $codeFormat = "????";
    $code = $this->service->generateCode($codeFormat);

    expect(strlen($code))->toEqual(strlen($codeFormat));
    foreach (str_split($code) as $codeChar) {
        expect($this->service->isUpperAlphaCharacter($codeChar))->toBeTrue();
    }
});

it('will bring back code in both alpha digit character format', function () {
    $codeFormat = "#?#?";
    $code = $this->service->generateCode($codeFormat);

    expect(strlen($code))->toEqual(strlen($codeFormat));
    expect($code[0])->toBeNumeric();
    expect($this->service->isUpperAlphaCharacter($code[1]))->toBeTrue();
    expect($code[2])->toBeNumeric();
    expect($this->service->isUpperAlphaCharacter($code[3]))->toBeTrue();
});

it('will generate two different codes', function () {
    $codeFormat = "#?#?";
    $firstCode = $this->service->generateCode($codeFormat);
    $secondCode = $this->service->generateCode($codeFormat);

    $this->assertNotEquals($firstCode, $secondCode);
});
