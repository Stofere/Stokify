<?php
// app/Helpers/format.php

if (! function_exists('format_jumlah')) {
    /**
     * Memformat angka jumlah berdasarkan satuannya.
     *
     * @param float|string $jumlah
     * @param string $satuan
     * @return string
     */
    function format_jumlah($jumlah, $satuan)
    {
        $decimalUnits = ['kg', 'meter'];
        $isDecimal = in_array(strtolower($satuan), $decimalUnits);

        return number_format((float) $jumlah, $isDecimal ? 2 : 0, ',', '.');
    }
}