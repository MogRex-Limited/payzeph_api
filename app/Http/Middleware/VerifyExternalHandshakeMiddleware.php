<?php

namespace App\Http\Middleware;

use App\Constants\General\ApiConstants;
use App\Exceptions\General\InvalidRequestException;
use App\Helpers\ApiHelper;
use App\Services\Auth\General\AuthorizationService;
use Closure;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class VerifyExternalHandshakeMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
    //  * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        try {
           AuthorizationService::verifyExternalApiKey($request);
        } catch (InvalidRequestException $e) {
            return ApiHelper::problemResponse($e->getMessage(), ApiConstants::BAD_REQ_ERR_CODE, $e);
        } catch (Exception $e) {
            logger($e->getMessage(), $e->getTrace());
            return ApiHelper::problemResponse("Something went wrong while trying to process your request", ApiConstants::SERVER_ERR_CODE, $e);
        }

        return $next($request);
    }
}
