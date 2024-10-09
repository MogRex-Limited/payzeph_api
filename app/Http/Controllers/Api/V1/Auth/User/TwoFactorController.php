<?php

namespace App\Http\Controllers\Api\V1\Auth\User;

use App\Constants\General\ApiConstants;
use App\Exceptions\Auth\AuthException;
use App\Helpers\ApiHelper;
use App\Http\Controllers\Controller;
use App\Services\Auth\TwoFactor\TwoFactorAuthService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class TwoFactorController extends Controller
{
    protected $two_factor_auth_service;
    public function __construct()
    {
        $this->two_factor_auth_service = new TwoFactorAuthService;
    }
    
    public function generateSecretKey(Request $request)
    {
        try {
            $data = $this->two_factor_auth_service->setModel(auth()->user())->generateSecretKey();
            return ApiHelper::validResponse("Two factor codes returned successfully", $data);
        } catch (Exception $e) {
            return ApiHelper::problemResponse($this->serverErrorMessage, ApiConstants::SERVER_ERR_CODE, $e);
        }
    }

    public function enable2FA(Request $request)
    {
        try {
            $data = $this->two_factor_auth_service->setModel(auth()->user())->enable2FA($request->all());
            return ApiHelper::validResponse("Two factor authentication enabled successfully");
        } catch (ValidationException $e) {
            return ApiHelper::inputErrorResponse($this->validationErrorMessage, ApiConstants::VALIDATION_ERR_CODE, $e);
        } catch (AuthException $e) {
            return ApiHelper::problemResponse($e->getMessage(), ApiConstants::BAD_REQ_ERR_CODE, $e);
        } catch (Exception $e) {
            return ApiHelper::problemResponse($this->serverErrorMessage, ApiConstants::SERVER_ERR_CODE, $e);
        }
    }

    public function disable2FA(Request $request)
    {
        try {
            $data = $this->two_factor_auth_service->setModel(auth()->user())->disable2FA();
            return ApiHelper::validResponse("Two factor authentication disabled successfully");
        } catch (Exception $e) {
            return ApiHelper::problemResponse($this->serverErrorMessage, ApiConstants::SERVER_ERR_CODE, $e);
        }
    }
}
