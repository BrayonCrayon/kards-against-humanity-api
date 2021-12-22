<?php

namespace App\Services;

class HelperService
{
    const UPPER_CASE_ALPHA_CHARACTERS = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    const UPPER_CASE_ALPHA_ASCII_START = 65;
    const UPPER_CASE_ALPHA_ASCII_END = 90;

    const LOWER_CASE_ALPHA_CHARACTERS = "abcdefghijklmnopqrstuvwxyz";
    const LOWER_CASE_ALPHA_ASCII_START = 97;
    const LOWER_CASE_ALPHA_ASCII_END = 122;

    const DIGIT_CHARACTERS = "0123456789";

    const NUMBER_SYMBOL = "#";
    const ALPHA_SYMBOL = "?";

    public function isUpperAlphaCharacter(string $char): bool
    {
        return ord($char) >= self::UPPER_CASE_ALPHA_ASCII_START && ord($char) <= self::UPPER_CASE_ALPHA_ASCII_END;
    }

    public function isLowerAlphaCharacter(string $char): bool
    {
        return ord($char) >= self::LOWER_CASE_ALPHA_ASCII_START && ord($char) <= self::LOWER_CASE_ALPHA_ASCII_END;
    }

    public function getRandomStrDigit(): string
    {
        return self::DIGIT_CHARACTERS[rand(0, strlen(self::DIGIT_CHARACTERS) - 1)];
    }

    public function getRandomUpperAlphaCharacter(): string
    {
        return self::UPPER_CASE_ALPHA_CHARACTERS[rand(0, strlen(self::UPPER_CASE_ALPHA_CHARACTERS) - 1)];
    }

    public function generateCode(string $codeFormat = ""): string
    {
        $code = "";

        foreach (str_split($codeFormat) as $indicator) {
            $code .= $this->generateRandomCharacter($indicator);
        }

        return $code;
    }

    public function generateRandomCharacter(string $indicator)
    {
        switch ($indicator) {
            case self::NUMBER_SYMBOL:
                return $this->getRandomStrDigit();
            case self::ALPHA_SYMBOL:
                return $this->getRandomUpperAlphaCharacter();
        }
    }
}
