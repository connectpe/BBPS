<?php

namespace App\Helpers;


class ApiResponseHelper
{

    public static function apiError($message, $extra = [], $code = "0x0201", $statusCode = 401)
    {
        return response()->json(array_merge([
            "code" => $code,
            "message" => $message,
            "status" => "FAILURE"
        ], $extra), $statusCode);
    }
}
