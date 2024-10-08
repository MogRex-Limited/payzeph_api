<?php

namespace App\Http\Controllers\Api\V1\Admin\Provider;

use App\Constants\General\ApiConstants;
use App\Exceptions\General\ModelNotFoundException;
use App\Helpers\ApiHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\Provider\ProviderResource;
use App\Services\Provider\ProviderService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ProviderController extends Controller
{
    protected $provider_service;

    function __construct()
    {
        $this->provider_service = new ProviderService;
    }

    public function index(Request $request)
    {
        try {
            $pricings = $this->provider_service->list()->get();
            $data = ProviderResource::collection($pricings);
            return ApiHelper::validResponse("Providers returned successfully", $data);
        } catch (Exception $th) {
            //throw $th;
            return ApiHelper::problemResponse($this->serverErrorMessage, ApiConstants::SERVER_ERR_CODE, $th);
        }
    }

    public function show($id)
    {
        try {
            $provider = $this->provider_service->getById($id);
            $data = ProviderResource::make($provider);
            return ApiHelper::validResponse("Provider details returned successfully", $data);
        } catch (Exception $th) {
            return ApiHelper::problemResponse($this->serverErrorMessage, ApiConstants::SERVER_ERR_CODE, $th);
        }
    }

    public function store(Request $request)
    {
        try {
            $provider = $this->provider_service->create($request->all());
            $data = ProviderResource::make($provider);
            return ApiHelper::validResponse("Provider created successfully", $data);
        } catch (ValidationException $th) {
            return ApiHelper::inputErrorResponse($this->validationErrorMessage, ApiConstants::VALIDATION_ERR_CODE, $th);
        } catch (ModelNotFoundException $th) {
            return ApiHelper::problemResponse($th->getMessage(), ApiConstants::BAD_REQ_ERR_CODE, $th);
        } catch (Exception $th) {
            return ApiHelper::problemResponse($this->serverErrorMessage, ApiConstants::SERVER_ERR_CODE, $th);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $provider = $this->provider_service->update($request->all(), $id);
            $data = ProviderResource::make($provider);
            return ApiHelper::validResponse("Provider updated successfully", $data);
        } catch (ValidationException $th) {
            return ApiHelper::inputErrorResponse($this->validationErrorMessage, ApiConstants::VALIDATION_ERR_CODE, $th);
        } catch (ModelNotFoundException $th) {
            return ApiHelper::problemResponse($th->getMessage(), ApiConstants::BAD_REQ_ERR_CODE, $th);
        } catch (Exception $th) {
            return ApiHelper::problemResponse($this->serverErrorMessage, ApiConstants::SERVER_ERR_CODE, $th);
        }
    }

    public function destroy($id)
    {
        try {
            $provider = $this->provider_service->getById($id);
            $provider->delete();
            return ApiHelper::validResponse("Provider deleted successfully");
        } catch (ModelNotFoundException $th) {
            return ApiHelper::problemResponse($th->getMessage(), ApiConstants::BAD_REQ_ERR_CODE, $th);
        } catch (Exception $th) {
            return ApiHelper::problemResponse($this->serverErrorMessage, ApiConstants::SERVER_ERR_CODE, $th);
        }
    }
}
