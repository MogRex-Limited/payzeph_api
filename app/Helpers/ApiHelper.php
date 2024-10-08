<?php

namespace App\Helpers;

use App\Constants\General\ApiConstants;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;


class ApiHelper
{
    static function problemResponse(string $message = null, int $status_code, Exception $trace = null)
    {
        $code = !empty($status_code) ? $status_code : null;
        $traceMsg = empty($trace) ?  null  : $trace->getMessage();

        $body = [
            'terminus' => request()->fullUrl(),
            'status' => "F9",
            'response' => [
                'title' => "Operation failed",
                'message' => $message,
                'code' => $code,
                "error_debug" => $traceMsg,
                "error_trace" => optional($trace)->getTrace()
            ]
        ];

        !empty($trace) ? logger($trace->getMessage(), $trace->getTrace()) : null;
        return response()->json($body)->setStatusCode($code);
    }

    /** Return error api response */
    static function inputErrorResponse(string $message = null, int $status_code = null, ValidationException $trace = null)
    {
        $code = ($status_code != null) ? $status_code : '';
        $traceMsg = empty($trace) ?  null  : $trace->getMessage();
        $traceTrace = empty($trace) ?  null  : $trace->getTrace();

        $body = [
            'terminus' => request()->fullUrl(),
            'status' => "F9",
            'response' => [
                'title' => "Operation failed",
                'message' => $message,
                'code' => $code,
                'errors' => empty($trace) ?  null  : $trace->errors(),
            ]
        ];

        return response()->json($body)->setStatusCode($code);
    }


    /** Return valid api response */
    static function validResponse(string $message = null, $data = null)
    {
        if (is_null($data) || empty($data)) {
            $data = [];
        }
        $body = [
            'terminus' => request()->fullUrl(),
            'status' => "OK",
            'response' => [
                'code' => ApiConstants::GOOD_REQ_CODE,
                'title' => "Operation successful",
                'message' => $message,
                'data' => $data,
            ]
        ];

        return response()->json($body)->setStatusCode(200);
    }


    /**Returns the available auth instance with user
     * @param bool $getUser
     */
    static function auth($getUser = false)
    {
        return $getUser ? auth("api")->user() : auth("api");
    }

    static function collectPagination(LengthAwarePaginator $pagination, $appendQuery = true)
    {
        $request = request();
        unset($request["token"]);
        if ($appendQuery) {
            $pagination->appends($request->query());
        }
        $all_pg_data = $pagination->toArray();
        unset($all_pg_data["links"]); // remove links
        unset($all_pg_data["data"]); // remove old data mapping

        $buildResponse["pagination_meta"] = $all_pg_data;
        $buildResponse["pagination_meta"]["can_load_more"] = $all_pg_data["to"] < $all_pg_data["total"];
        // $buildResponse["pagination_meta"]["query"] = $request->query();
        $buildResponse["data"] = $pagination->getCollection();
        return $buildResponse;
    }

}
