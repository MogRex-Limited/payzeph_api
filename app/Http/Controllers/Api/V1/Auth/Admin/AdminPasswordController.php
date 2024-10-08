<?php

namespace App\Http\Controllers\Api\V1\Auth\Admin;

use App\Constants\General\ApiConstants;
use App\Exceptions\Auth\AuthException;
use App\Exceptions\Auth\PinException;
use App\Helpers\ApiHelper;
use App\Http\Controllers\Controller;
use App\Services\Auth\Admin\AdminPasswordService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Exception;

class AdminPasswordController extends Controller
{
    public $admin_pasword_service;
    function __construct()
    {
        $this->admin_pasword_service = new AdminPasswordService;
    }

    public function forgotPassword(Request $request)
    {
        try {
            $this->admin_pasword_service->sendPasswordResetPin($request->all());
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
            $this->admin_pasword_service->resetPassword($request->all());
            return ApiHelper::validResponse("Password reset successfully");
        } catch (ValidationException $e) {
            return ApiHelper::inputErrorResponse("Invalid data", ApiConstants::VALIDATION_ERR_CODE, $e);
        } catch (PinException $e) {
            return ApiHelper::problemResponse($e->getMessage(), ApiConstants::BAD_REQ_ERR_CODE, $e);
        } catch (Exception $e) {
            return ApiHelper::problemResponse(
                "Something went wrong while processing your request",
                ApiConstants::SERVER_ERR_CODE,
                $e
            );
        }
    }
}
