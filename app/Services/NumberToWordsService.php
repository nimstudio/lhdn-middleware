<?php

namespace App\Services;

class NumberToWordsService
{
    private static $ones = [
        0 => '', 1 => 'ONE', 2 => 'TWO', 3 => 'THREE', 4 => 'FOUR', 5 => 'FIVE',
        6 => 'SIX', 7 => 'SEVEN', 8 => 'EIGHT', 9 => 'NINE', 10 => 'TEN',
        11 => 'ELEVEN', 12 => 'TWELVE', 13 => 'THIRTEEN', 14 => 'FOURTEEN', 15 => 'FIFTEEN',
        16 => 'SIXTEEN', 17 => 'SEVENTEEN', 18 => 'EIGHTEEN', 19 => 'NINETEEN'
    ];

    private static $tens = [
        2 => 'TWENTY', 3 => 'THIRTY', 4 => 'FORTY', 5 => 'FIFTY',
        6 => 'SIXTY', 7 => 'SEVENTY', 8 => 'EIGHTY', 9 => 'NINETY'
    ];

    private static $thousands = [
        '', 'THOUSAND', 'MILLION', 'BILLION', 'TRILLION'
    ];

    public static function convert($number)
    {
        if ($number == 0) {
            return 'ZERO';
        }

        $number = number_format($number, 2, '.', '');
        $parts = explode('.', $number);
        $dollars = (int) $parts[0];
        $cents = isset($parts[1]) ? (int) $parts[1] : 0;

        $result = 'MALAYSIAN RINGGIT ' . self::convertGroup($dollars);

        if ($cents > 0) {
            $result .= ' & CENTS ' . self::convertGroup($cents);
        }

        return $result;
    }

    private static function convertGroup($number)
    {
        if ($number == 0) {
            return '';
        }

        $result = '';
        $groupIndex = 0;

        while ($number > 0) {
            $group = $number % 1000;
            if ($group != 0) {
                $groupWords = self::convertHundreds($group);
                if ($groupIndex > 0) {
                    $groupWords .= ' ' . self::$thousands[$groupIndex];
                }
                $result = $groupWords . ($result ? ' ' . $result : '');
            }
            $number = intval($number / 1000);
            $groupIndex++;
        }

        return $result;
    }

    private static function convertHundreds($number)
    {
        $result = '';

        // Hundreds
        if ($number >= 100) {
            $hundreds = intval($number / 100);
            $result .= self::$ones[$hundreds] . ' HUNDRED';
            $number %= 100;
        }

        // Tens and Ones
        if ($number >= 20) {
            $tens = intval($number / 10);
            $result .= ($result ? ' ' : '') . self::$tens[$tens];
            $number %= 10;
            if ($number > 0) {
                $result .= ' ' . self::$ones[$number];
            }
        } elseif ($number > 0) {
            $result .= ($result ? ' ' : '') . self::$ones[$number];
        }

        return $result;
    }
}



