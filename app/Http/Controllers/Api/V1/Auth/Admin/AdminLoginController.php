<?php

namespace App\Http\Controllers\Api\V1\Auth\Admin;

use App\Constants\General\ApiConstants;
use App\Exceptions\Auth\AuthException;
use App\Helpers\ApiHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\AdminResource;
use App\Services\Auth\Admin\AdminLoginService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AdminLoginController extends Controller
{
    public function login(Request $request)
    {
        try {
            $admin = AdminLoginService::authenticate($request->all());
            $data["token"] = $admin->createToken('new-token')->plainTextToken;
            $data["admin"] = AdminResource::make($admin)->toArray($request);
            AdminLoginService::newLogin($admin);
            return ApiHelper::validResponse("Logged in successfully", $data);
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
            $admin = auth("admin")->user();
            $admin->currentAccessToken()->delete();
            return ApiHelper::validResponse("Logout successful", null, $request);
        } catch (Exception $e) {
            return ApiHelper::problemResponse("Something went wrong while trying to process your request", ApiConstants::SERVER_ERR_CODE, $e);
        }
    }
}
