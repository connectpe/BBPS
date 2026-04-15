<?php

namespace App\Helpers;


class ApiResponseHelper
{
    protected const SUCCESS_CODE = "0x0200";
    protected const SUCCESS_STATUS = "SUCCESS";

    protected const UNAUTHORIZED_CODE = "0x0201";
    protected const UNAUTHORIZED_STATUS = "UNAUTHORIZED";

    protected const FAILED_CODE = "0x0202";
    protected const FAILED_STATUS = "FAILURE";

    protected const MISSING_PARAMETER_CODE = "0x0203";
    protected const MISSING_PARAMETER_STATUS = "MISSING_PARAMETER";

    public static function apiError($message, $extra = [], $code = "0x0201", $statusCode = 401)
    {
        return response()->json(array_merge([
            "code" => $code,
            "message" => $message,
            "status" => "FAILURE"
        ], $extra), $statusCode);
    }

    public static function missing($message = 'Missing Parameters', $data = [], $respCode = '200')
    {
        $res['code'] = self::MISSING_PARAMETER_CODE;
        $res["status"] = self::MISSING_PARAMETER_STATUS;
        $res["message"] = $message;

        if ($data) {
            $res["data"] = $data;
        }

        return response()->json($res, $respCode);
    }

    public static function success($message = 'Success', $data = [], $respCode = '200')
    {
        $res['code'] = self::SUCCESS_CODE;
        $res["message"] = $message;
        $res["status"] = self::SUCCESS_STATUS;

        if ($data) {
            $res["data"] = $data;
        }

        return response()->json($res, $respCode);
    }

    public static function unauthorized($message = 'Unauthorized', $data = [],  $respCode = '401')
    {
        $res['code'] = self::UNAUTHORIZED_CODE;
        $res["message"] = $message;
        $res["status"] = self::UNAUTHORIZED_STATUS;

        if ($data) {
            $res["data"] = $data;
        }

        return response()->json($res, $respCode);
    }

    public static function failed($message = 'Failure', $data = [], $respCode = '200')
    {
        $res['code'] = self::FAILED_CODE;
        $res["status"] = self::FAILED_STATUS;
        $res["message"] = $message;

        if ($data) {
            $res["data"] = $data;
        }

        return response()->json($res, $respCode);
    }
}
