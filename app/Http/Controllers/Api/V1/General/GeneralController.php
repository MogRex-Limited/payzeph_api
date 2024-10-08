<?php

namespace App\Http\Controllers\Api\V1\General;

use App\Constants\General\ApiConstants;
use App\Exceptions\Auth\AuthException;
use App\Helpers\ApiHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\AdminResource;
use App\Http\Resources\User\UserResource;
use App\Services\Auth\Admin\AdminLoginService;
use App\Services\Auth\User\UserLoginService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class GeneralController extends Controller
{
    public function previewAdmin(Request $request)
    {
        try {
            $admin = AdminLoginService::preview($request->all());
            return ApiHelper::validResponse("Admin data retrieved successfully", AdminResource::make($admin));
        } catch (ValidationException $e) {
            return ApiHelper::inputErrorResponse($this->validationErrorMessage, ApiConstants::VALIDATION_ERR_CODE, $e);
        } catch (AuthException $e) {
            return ApiHelper::problemResponse($e->getMessage(), ApiConstants::BAD_REQ_ERR_CODE, $e);
        } catch (Exception $e) {
            return ApiHelper::problemResponse($this->serverErrorMessage, ApiConstants::SERVER_ERR_CODE, $e);
        }
    }

    public function previewUser(Request $request)
    {
        try {
            $user = UserLoginService::preview($request->all());
            return ApiHelper::validResponse("User data retrieved successfully", UserResource::make($user));
        } catch (ValidationException $e) {
            return ApiHelper::inputErrorResponse($this->validationErrorMessage, ApiConstants::VALIDATION_ERR_CODE, $e);
        } catch (AuthException $e) {
            return ApiHelper::problemResponse($e->getMessage(), ApiConstants::BAD_REQ_ERR_CODE, $e);
        } catch (Exception $e) {
            return ApiHelper::problemResponse($this->serverErrorMessage, ApiConstants::SERVER_ERR_CODE, $e);
        }
    }
}
