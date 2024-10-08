<?php

namespace App\Http\Controllers\Api\V1\Auth\User;

use App\Constants\General\ApiConstants;
use App\Exceptions\Auth\AuthException;
use App\Exceptions\Auth\PinException;
use App\Helpers\ApiHelper;
use App\Http\Controllers\Controller;
use App\Services\Auth\User\UserPasswordService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class UserPasswordController extends Controller
{
    public $user_pasword_service;
    function __construct()
    {
        $this->user_pasword_service = new UserPasswordService;
    }

    public function forgotPassword(Request $request)
    {
        try {
            $this->user_pasword_service->sendPasswordResetPin($request->all());
            return ApiHelper::validResponse("Password request sent successfully!");
        } catch (ValidationException $e) {
            return ApiHelper::inputErrorResponse($this->validationErrorMessage, ApiConstants::VALIDATION_ERR_CODE, $e);
        } catch (AuthException | PinException $e) {
            return ApiHelper::problemResponse($e->getMessage(), ApiConstants::BAD_REQ_ERR_CODE, $e);
        } catch (Exception $e) {
            // throw $e;
            return ApiHelper::problemResponse(
                $this->serverErrorMessage,
                ApiConstants::SERVER_ERR_CODE,
                $e
            );
        }
    }



    public function resetPassword(Request $request)
    {
        try {
            $this->user_pasword_service->resetPassword($request->all());
            return ApiHelper::validResponse("Password reset successfully");
        } catch (ValidationException $e) {
            return ApiHelper::inputErrorResponse("Invalid data", ApiConstants::VALIDATION_ERR_CODE, $e);
        } catch (PinException $e) {
            return ApiHelper::problemResponse($e->getMessage(), ApiConstants::BAD_REQ_ERR_CODE, $e);
        } catch (Exception $e) {
            return ApiHelper::problemResponse(
                $this->serverErrorMessage,
                ApiConstants::SERVER_ERR_CODE,
                $e
            );
        }
    }
}
