<?php

namespace App\Http\Controllers\Api\V1\Auth\User;

use App\Constants\General\ApiConstants;
use App\Exceptions\Auth\PinException;
use App\Helpers\ApiHelper;
use App\Http\Controllers\Controller;
use App\Services\Auth\User\UserVerifyService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UserVerificationController extends Controller
{
    public $user_verify_service;
    function __construct()
    {
        $this->user_verify_service = new UserVerifyService;
    }
    public function request(Request $request)
    {
        try {
            $this->user_verify_service->request($request->all());
            return ApiHelper::validResponse("Verification pin sent successfully");
        } catch (ValidationException $e) {
            return ApiHelper::inputErrorResponse($this->validationErrorMessage, ApiConstants::VALIDATION_ERR_CODE, null, $e);
        } catch (Exception $e) {
            return ApiHelper::problemResponse($this->serverErrorMessage, ApiConstants::SERVER_ERR_CODE, null, $e);
        }
    }

    public function verify(Request $request)
    {
        try {
            $this->user_verify_service->verify($request->all());
            return ApiHelper::validResponse("Email verified successfully");
        } catch (ValidationException $e) {
            return ApiHelper::inputErrorResponse($this->validationErrorMessage, ApiConstants::VALIDATION_ERR_CODE, null, $e);
        } catch (PinException $e) {
            return ApiHelper::problemResponse($e->getMessage(), ApiConstants::BAD_REQ_ERR_CODE);
        } catch (Exception $e) {
            return ApiHelper::problemResponse($this->serverErrorMessage, ApiConstants::SERVER_ERR_CODE, null, $e);
        }
    }
}
