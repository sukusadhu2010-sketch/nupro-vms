<?php

namespace App\Support;

class Amount
{
    /**
     * Tax percentages configured for the business.
     * IGST @18%  OR  (SGST @9% + CGST @9%) — both sum to 18%.
     */
    public const IGST_RATE = 18.00;
    public const SGST_RATE = 9.00;
    public const CGST_RATE = 9.00;

    /**
     * Calculate tax breakdown for a taxable amount (quantity amount).
     *
     * @param float $taxable Quantity amount (subtotal)
     * @param string $taxType igst|sgst_cgst (default igst)
     * @return array{tax_type:string, igst:float, sgst:float, cgst:float, tax_amount:float, grand_total:float}
     */
    public static function taxes(float $taxable, string $taxType = 'igst'): array
    {
        $taxType = $taxType === 'sgst_cgst' ? 'sgst_cgst' : 'igst';

        $igst = $sgst = $cgst = 0.0;
        if ($taxType === 'igst') {
            $igst = round($taxable * (self::IGST_RATE / 100), 2);
        } else {
            $sgst = round($taxable * (self::SGST_RATE / 100), 2);
            $cgst = round($taxable * (self::CGST_RATE / 100), 2);
        }

        $taxAmount = round($igst + $sgst + $cgst, 2);

        return [
            'tax_type' => $taxType,
            'igst' => $igst,
            'sgst' => $sgst,
            'cgst' => $cgst,
            'tax_amount' => $taxAmount,
            'grand_total' => round($taxable + $taxAmount, 2),
        ];
    }

    /**
     * Convert a numeric amount to words (Indian numbering: crore / lakh / thousand).
     * e.g. 123456.78 => "One Lakh Twenty Three Thousand Four Hundred Fifty Six Rupees And Seventy Eight Paise Only"
     */
    public static function inWords(float $amount): string
    {
        $amount = round($amount, 2);
        $rupees = (int) floor($amount);
        $paise = (int) round(($amount - $rupees) * 100);

        $words = self::numberToWords($rupees) . ' Rupees';
        if ($paise > 0) {
            $words .= ' And ' . self::numberToWords($paise) . ' Paise';
        }

        return trim($words) . ' Only';
    }

    private static function numberToWords(int $number): string
    {
        if ($number === 0) {
            return 'Zero';
        }

        $ones = [
            '', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine',
            'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen',
            'Seventeen', 'Eighteen', 'Nineteen',
        ];
        $tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];

        $parts = [];

        $crore = intdiv($number, 10000000);
        $number %= 10000000;

        $lakh = intdiv($number, 100000);
        $number %= 100000;

        $thousand = intdiv($number, 1000);
        $number %= 1000;

        $hundred = intdiv($number, 100);
        $remainder = $number % 100;

        if ($crore > 0) {
            $parts[] = self::numberToWords($crore) . ' Crore';
        }
        if ($lakh > 0) {
            $parts[] = self::twoDigits($lakh, $ones, $tens) . ' Lakh';
        }
        if ($thousand > 0) {
            $parts[] = self::twoDigits($thousand, $ones, $tens) . ' Thousand';
        }
        if ($hundred > 0) {
            $parts[] = $ones[$hundred] . ' Hundred';
        }
        if ($remainder > 0) {
            $parts[] = self::twoDigits($remainder, $ones, $tens);
        }

        return implode(' ', $parts);
    }

    private static function twoDigits(int $n, array $ones, array $tens): string
    {
        if ($n < 20) {
            return $ones[$n];
        }
        return trim($tens[intdiv($n, 10)] . ($n % 10 > 0 ? ' ' . $ones[$n % 10] : ''));
    }
}
