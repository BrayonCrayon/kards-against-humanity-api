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

    public function isUpperAlphaCharacter(string $char): bool
    {
        return ord($char) >= self::UPPER_CASE_ALPHA_ASCII_START && ord($char) <= self::UPPER_CASE_ALPHA_ASCII_END;
    }

    public function isLowerAlphaCharacter(string $char): bool
    {
        return ord($char) >= self::LOWER_CASE_ALPHA_ASCII_START && ord($char) <= self::LOWER_CASE_ALPHA_ASCII_END;
    }

    public function generateCode(string $codeFormat = ""): string
    {
        $code = "";

        foreach (str_split($codeFormat) as $item) {
            if ($item === "#")
                $code .= self::DIGIT_CHARACTERS[rand(0, strlen(self::DIGIT_CHARACTERS) - 1)];
            else if ($item === "?")
                $code .= self::UPPER_CASE_ALPHA_CHARACTERS[rand(0, strlen(self::UPPER_CASE_ALPHA_CHARACTERS) - 1)];
        }

        return $code;
    }
}
