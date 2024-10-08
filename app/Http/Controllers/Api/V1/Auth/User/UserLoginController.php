<?php

namespace App\Http\Controllers\Api\V1\Auth\User;

use App\Constants\General\ApiConstants;
use App\Exceptions\Auth\AuthException;
use App\Helpers\ApiHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\User\PreviewResource;
use App\Http\Resources\User\UserResource;
use App\Services\Auth\General\SanctumService;
use App\Services\Auth\User\UserLoginService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class UserLoginController extends Controller
{
    public function login(Request $request)
    {
        try {
            $user = UserLoginService::authenticate($request->all());
            $data["token"] = SanctumService::createAccountToken($user);
            $data["user"] = UserResource::make($user);
            UserLoginService::newLogin($user);
            return ApiHelper::validResponse("Logged in successfully", $data);
        } catch (ValidationException $e) {
            return ApiHelper::inputErrorResponse($this->validationErrorMessage, ApiConstants::VALIDATION_ERR_CODE, $e);
        } catch (AuthException $e) {
            return ApiHelper::problemResponse($e->getMessage(), ApiConstants::BAD_REQ_ERR_CODE, $e);
        } catch (Exception $e) {
            return ApiHelper::problemResponse($this->serverErrorMessage, ApiConstants::SERVER_ERR_CODE, $e);
        }
    }

    public function fetchAccounts(Request $request)
    {
        try {
            $user = auth()->user();
            return ApiHelper::validResponse("User data retrieved successfully", PreviewResource::make($user));
        } catch (ValidationException $e) {
            return ApiHelper::inputErrorResponse($this->validationErrorMessage, ApiConstants::VALIDATION_ERR_CODE, $e);
        } catch (AuthException $e) {
            return ApiHelper::problemResponse($e->getMessage(), ApiConstants::BAD_REQ_ERR_CODE, $e);
        } catch (Exception $e) {
            return ApiHelper::problemResponse($this->serverErrorMessage, ApiConstants::SERVER_ERR_CODE, $e);
        }
    }

    public function logout(Request $request)
    {
        try {
            $user = auth()->user();
            $user->currentAccessToken()->delete();
            return ApiHelper::validResponse("Logout successful", null);
        } catch (Exception $e) {
            return ApiHelper::problemResponse("Something went wrong while trying to process your request", ApiConstants::SERVER_ERR_CODE, $e);
        }
    }
}
