<?php

namespace App\Traits;

trait TerbilangTrait
{
    /**
     * Converts a number segment into Indonesian words.
     * @param int|float $number
     * @return string
     */
    protected function terbilang(int|float $number): string
    {
        $words = [
            '', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas'
        ];

        $number = abs($number);
        $result = '';

        if ($number < 12) {
            $result = $words[$number];
        } elseif ($number < 20) {
            $result = $words[$number - 10] . ' belas';
        } elseif ($number < 100) {
            $result = $words[floor($number / 10)] . ' puluh ' . $this->terbilang($number % 10);
        } elseif ($number < 200) {
            $result = 'seratus ' . $this->terbilang($number - 100);
        } elseif ($number < 1000) {
            $result = $words[floor($number / 100)] . ' ratus ' . $this->terbilang($number % 100);
        } elseif ($number < 2000) {
            $result = 'seribu ' . $this->terbilang($number - 1000);
        } elseif ($number < 1000000) { // Million
            $result = $this->terbilang(floor($number / 1000)) . ' ribu ' . $this->terbilang($number % 1000);
        } elseif ($number < 1000000000) { // Billion
            $result = $this->terbilang(floor($number / 1000000)) . ' juta ' . $this->terbilang($number % 1000000);
        } elseif ($number < 1000000000000) { // Trillion
            $result = $this->terbilang(floor($number / 1000000000)) . ' milyar ' . $this->terbilang($number % 1000000000);
        } elseif ($number < 1000000000000000) { // Quadrillion
            $result = $this->terbilang(floor($number / 1000000000000)) . ' triliun ' . $this->terbilang($number % 1000000000000);
        }

        return preg_replace('/\s+/', ' ', $result); // Clean up extra spaces
    }

    /**
     * Formats the final amount in words with currency suffix.
     * @param int|float $amount The total amount.
     * @return string
     */
    public function terbilangRupiah(int|float $amount): string
    {
        // Use floor to ensure only the integer part (Rupiah) is converted.
        $integerPart = floor($amount);
        $result = trim($this->terbilang($integerPart)) . ' rupiah'; // Lowercase currency
        
        // Final format: Capitalize only the first letter.
        return ucwords($result);
    }
}