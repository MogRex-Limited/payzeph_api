<?php

namespace App\Http\Controllers\Api\V1\Auth\Admin;

use App\Constants\General\ApiConstants;
use App\Helpers\ApiHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\AdminResource;
use App\Services\Auth\Admin\AdminRegistrationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Exception;

class AdminRegisterController extends Controller
{
    public $register_service;
    function __construct()
    {
        $this->register_service = new AdminRegistrationService;
    }
    public function register(Request $request)
    {
        DB::beginTransaction();
        try {
            $admin = $this->register_service->create($request->all());
            $data["token"] = $admin->createToken('auth-admin')->plainTextToken;
            $data["admin"] =  AdminResource::make($admin);
            $this->register_service->postRegisterActions($admin);
            DB::commit();
            return ApiHelper::validResponse("Admin registered successfully", $data);
        } catch (ValidationException $e) {
            DB::rollBack();
            return ApiHelper::inputErrorResponse($this->validationErrorMessage, ApiConstants::VALIDATION_ERR_CODE, $e);
        } catch (Exception $e) {
            DB::rollBack();
            return ApiHelper::problemResponse(
                $this->serverErrorMessage,
                ApiConstants::SERVER_ERR_CODE,
                $e
            );
        }
    }
}
