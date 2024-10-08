<?php

namespace App\Http\Controllers\Api\V1\Admin\Provider;

use App\Constants\General\ApiConstants;
use App\Exceptions\General\ModelNotFoundException;
use App\Helpers\ApiHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\Provider\ProviderRouteResource;
use App\Services\Provider\ProviderRouteService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ProviderRouteController extends Controller
{
    protected $provider_route_service;

    function __construct()
    {
        $this->provider_route_service = new ProviderRouteService;
    }

    public function index(Request $request)
    {
        try {
            $pricings = $this->provider_route_service->list($request->all())->get();
            $data = ProviderRouteResource::collection($pricings);
            return ApiHelper::validResponse("Provider routes returned successfully", $data);
        } catch (Exception $th) {
            //throw $th;
            return ApiHelper::problemResponse($this->serverErrorMessage, ApiConstants::SERVER_ERR_CODE, $th);
        }
    }

    public function show($id)
    {
        try {
            $provider_route = $this->provider_route_service->getById($id);
            $data = ProviderRouteResource::make($provider_route);
            return ApiHelper::validResponse("Provider route details returned successfully", $data);
        } catch (Exception $th) {
            return ApiHelper::problemResponse($this->serverErrorMessage, ApiConstants::SERVER_ERR_CODE, $th);
        }
    }

    public function store(Request $request)
    {
        try {
            $provider_route = $this->provider_route_service->create($request->all());
            $data = ProviderRouteResource::make($provider_route);
            return ApiHelper::validResponse("Provider route created successfully", $data);
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
            $provider_route = $this->provider_route_service->update($request->all(), $id);
            $data = ProviderRouteResource::make($provider_route);
            return ApiHelper::validResponse("Provider route updated successfully", $data);
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
            $provider_route = $this->provider_route_service->getById($id);
            $provider_route->delete();
            return ApiHelper::validResponse("Provider route deleted successfully");
        } catch (ModelNotFoundException $th) {
            return ApiHelper::problemResponse($th->getMessage(), ApiConstants::BAD_REQ_ERR_CODE, $th);
        } catch (Exception $th) {
            return ApiHelper::problemResponse($this->serverErrorMessage, ApiConstants::SERVER_ERR_CODE, $th);
        }
    }
}
