<?php

if (!function_exists('maskValue')) {
    function maskValue(string $value, int $visible = 4): string
    {
        if (strlen($value) <= $visible) {
            return $value;
        }

        return substr($value, 0, $visible)
            . str_repeat('*', strlen($value) - $visible);
    }
}
