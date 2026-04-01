<?php

namespace App\Helpers;

class NumberToWords
{
    public static function numberToWords($number)
    {
        $units = ["", "One", "Two", "Three", "Four", "Five", "Six", "Seven", "Eight", "Nine"];
        $teens = ["Ten", "Eleven", "Twelve", "Thirteen", "Fourteen", "Fifteen", "Sixteen", "Seventeen", "Eighteen", "Nineteen"];
        $tens = ["", "Ten", "Twenty", "Thirty", "Forty", "Fifty", "Sixty", "Seventy", "Eighty", "Ninety"];

        if ($number == 0) return "Zero";

        $words = "";
        if ($number < 0) {
            $words = "Minus ";
            $number = abs($number);
        }

        if ($number < 10) {
            $words .= $units[$number];
        } elseif ($number < 20) {
            $words .= $teens[$number - 10];
        } elseif ($number < 100) {
            $words .= $tens[intval($number / 10)] . " " . $units[$number % 10];
        } elseif ($number < 1000) {
            $words .= $units[intval($number / 100)] . " Hundred " . self::numberToWords($number % 100);
        } elseif ($number < 1000000) {
            $words .= self::numberToWords(intval($number / 1000)) . " Thousand " . self::numberToWords($number % 1000);
        } elseif ($number < 1000000000) {
            $words .= self::numberToWords(intval($number / 1000000)) . " Million " . self::numberToWords($number % 1000000);
        } else {
            $words .= self::numberToWords(intval($number / 1000000000)) . " Billion " . self::numberToWords($number % 1000000000);
        }

        return trim($words);
    }
}
