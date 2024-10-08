<?php

namespace App\Http\Controllers\Api\V1\Auth\User;

use App\Constants\General\ApiConstants;
use App\Helpers\ApiHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\User\UserResource;
use App\Services\Auth\User\UserRegistrationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Exception;

class UserRegisterController extends Controller
{
    public $register_service;
    function __construct()
    {
        $this->register_service = new UserRegistrationService;
    }
    public function register(Request $request)
    {
        DB::beginTransaction();
        try {
            $user = $this->register_service->create($request->all());
            $data["token"] = $user->createToken('auth-user')->plainTextToken;
            $data["user"] =  UserResource::make($user)->toArray($request);
            $this->register_service->postRegisterActions($user);
            DB::commit();
            return ApiHelper::validResponse("User registered successfully", $data);
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
