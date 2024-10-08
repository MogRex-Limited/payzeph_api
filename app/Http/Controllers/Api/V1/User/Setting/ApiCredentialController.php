<?php

namespace App\Http\Controllers\Api\V1\User\Setting;

use App\Constants\General\ApiConstants;
use App\Exceptions\General\ModelNotFoundException;
use App\Helpers\ApiHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\Setting\ApiCredentialResource;
use App\Models\ApiCredential;
use App\Services\Setting\ApiCredentialService;
use App\Services\System\ExceptionService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ApiCredentialController extends Controller
{
    public $api_credential_service;
    public function __construct()
    {
        $this->api_credential_service = new ApiCredentialService;
    }

    public function index()
    {
        try {
            $user = auth()->user();
            $api_credential = ApiCredential::where("user_id", $user->id)->latest()->first();

            if (empty($api_credential)) {
                $api_credential = $this->generateNewAuth($user);
            }

            $data = ApiCredentialResource::make($api_credential);
            return ApiHelper::validResponse("Credentials returned successfully", $data);
        } catch (ValidationException $th) {
            return ApiHelper::inputErrorResponse($this->validationErrorMessage, ApiConstants::VALIDATION_ERR_CODE, $th);
        } catch (Exception $th) {
            return ApiHelper::problemResponse($this->serverErrorMessage, ApiConstants::SERVER_ERR_CODE, $th);
        }
    }

    public function generateNewAuth($user)
    {
        try {
            return $this->api_credential_service
                ->generateKeys()->save([
                    "user_id" => $user->id,
                ]);
        } catch (Exception $th) {
            ExceptionService::logAndBroadcast($th);
            throw $th;
        }
    }

    public function refresh(Request $request)
    {
        try {
            $api_credential = $this->api_credential_service->getByUserId(auth()->id());
            $api_credential = $this->api_credential_service
                ->generateKeys()->refresh($api_credential->id);
                
            $data = ApiCredentialResource::make($api_credential);
            return ApiHelper::validResponse("Credentials refreshed successfully", $data);
        } catch (Exception $th) {
            return ApiHelper::problemResponse($this->serverErrorMessage, ApiConstants::SERVER_ERR_CODE, $th);
        }
    }

    public function update(Request $request)
    {
        try {
            $api_credential = $this->api_credential_service->getByUserId(auth()->id());
            $api_credential = $this->api_credential_service->update($request->all(), $api_credential->id);
            $data = ApiCredentialResource::make($api_credential);
            return ApiHelper::validResponse("Credentials updated successfully", $data);
        } catch (ValidationException $th) {
            return ApiHelper::inputErrorResponse($this->validationErrorMessage, ApiConstants::VALIDATION_ERR_CODE, $th);
        } catch (ModelNotFoundException $th) {
            return ApiHelper::problemResponse($th->getMessage(), ApiConstants::BAD_REQ_ERR_CODE, $th);
        } catch (Exception $th) {
            return ApiHelper::problemResponse($this->serverErrorMessage, ApiConstants::SERVER_ERR_CODE, $th);
        }
    }
}
