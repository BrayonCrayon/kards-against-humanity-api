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
                $code .= '1';
            else if ($item === "?")
                $code .= 'A';
        }

        return $code;
    }
}
