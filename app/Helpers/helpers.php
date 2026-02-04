<?php

if (!function_exists('maskValue')) {
    function maskValue(string $value, int $visible = 4, int $totalLength = 10): string
    {
        // If value is shorter than visible chars, return as-is
        if (strlen($value) <= $visible) {
            return str_pad($value, $totalLength, '*');
        }

        $visiblePart = substr($value, 0, $visible);

        return $visiblePart . str_repeat('*', max(0, $totalLength - $visible));
    }
}
