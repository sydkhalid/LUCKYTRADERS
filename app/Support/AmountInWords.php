<?php

namespace App\Support;

class AmountInWords
{
    private const ONES = [
        0 => 'Zero',
        1 => 'One',
        2 => 'Two',
        3 => 'Three',
        4 => 'Four',
        5 => 'Five',
        6 => 'Six',
        7 => 'Seven',
        8 => 'Eight',
        9 => 'Nine',
        10 => 'Ten',
        11 => 'Eleven',
        12 => 'Twelve',
        13 => 'Thirteen',
        14 => 'Fourteen',
        15 => 'Fifteen',
        16 => 'Sixteen',
        17 => 'Seventeen',
        18 => 'Eighteen',
        19 => 'Nineteen',
    ];

    private const TENS = [
        20 => 'Twenty',
        30 => 'Thirty',
        40 => 'Forty',
        50 => 'Fifty',
        60 => 'Sixty',
        70 => 'Seventy',
        80 => 'Eighty',
        90 => 'Ninety',
    ];

    public static function rupees(float|int|string $amount): string
    {
        $amount = round((float) $amount, 2);
        $prefix = $amount < 0 ? 'Minus ' : '';
        $amount = abs($amount);
        $rupees = (int) floor($amount);
        $paise = (int) round(($amount - $rupees) * 100);

        if ($paise === 100) {
            $rupees++;
            $paise = 0;
        }

        $words = $prefix.self::numberToWords($rupees).' Rupees';

        if ($paise > 0) {
            $words .= ' and '.self::numberToWords($paise).' Paise';
        }

        return $words.' Only';
    }

    private static function numberToWords(int $number): string
    {
        if ($number === 0) {
            return self::ONES[0];
        }

        $parts = [];

        foreach ([
            10000000 => 'Crore',
            100000 => 'Lakh',
            1000 => 'Thousand',
            100 => 'Hundred',
        ] as $value => $label) {
            if ($number >= $value) {
                $count = intdiv($number, $value);
                $parts[] = self::numberToWords($count).' '.$label;
                $number %= $value;
            }
        }

        if ($number > 0) {
            $parts[] = self::belowHundred($number);
        }

        return implode(' ', $parts);
    }

    private static function belowHundred(int $number): string
    {
        if ($number < 20) {
            return self::ONES[$number];
        }

        $ten = intdiv($number, 10) * 10;
        $unit = $number % 10;

        return self::TENS[$ten].($unit > 0 ? ' '.self::ONES[$unit] : '');
    }
}
