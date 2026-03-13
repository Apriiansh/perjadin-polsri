<?php

if (!function_exists('terbilang')) {
    /**
     * Convert number to Indonesian words
     *
     * @param int|float $number
     * @return string
     */
    function terbilang($number)
    {
        $number = abs($number);
        $words = ["", "satu", "dua", "tiga", "empat", "lima", "enam", "tujuh", "delapan", "sembilan", "sepuluh", "sebelas"];
        $result = "";

        if ($number < 12) {
            $result = " " . $words[$number];
        } elseif ($number < 20) {
            $result = terbilang($number - 10) . " belas";
        } elseif ($number < 100) {
            $result = terbilang((int)($number / 10)) . " puluh" . terbilang($number % 10);
        } elseif ($number < 200) {
            $result = " seratus" . terbilang($number - 100);
        } elseif ($number < 1000) {
            $result = terbilang((int)($number / 100)) . " ratus" . terbilang($number % 100);
        } elseif ($number < 2000) {
            $result = " seribu" . terbilang($number - 1000);
        } elseif ($number < 1000000) {
            $result = terbilang((int)($number / 1000)) . " ribu" . terbilang($number % 1000);
        } elseif ($number < 1000000000) {
            $result = terbilang((int)($number / 1000000)) . " juta" . terbilang($number % 1000000);
        } elseif ($number < 1000000000000) {
            $result = terbilang((int)($number / 1000000000)) . " milyar" . terbilang($number % 1000000000);
        } elseif ($number < 1000000000000000) {
            $result = terbilang((int)($number / 1000000000000)) . " trilyun" . terbilang($number % 1000000000000);
        }

        return $result;
    }
}

if (!function_exists('terbilang_rupiah')) {
    /**
     * Convert number to Indonesian words with 'rupiah' suffix
     *
     * @param int|float $number
     * @return string
     */
    function terbilang_rupiah($number)
    {
        $result = trim(terbilang($number));
        if ($result === "") {
            $result = "nol";
        }
        return ucfirst($result) . " rupiah";
    }
}
