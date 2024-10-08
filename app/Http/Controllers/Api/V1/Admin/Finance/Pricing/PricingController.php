<?php

namespace App\Http\Controllers\Api\V1\Admin\Finance\Pricing;

use App\Constants\General\ApiConstants;
use App\Exceptions\General\ModelNotFoundException;
use App\Helpers\ApiHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\Finance\Pricing\PricingResource;
use App\Services\Finance\Pricing\PricingService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PricingController extends Controller
{
    protected $pricing_service;

    function __construct()
    {
        $this->pricing_service = new PricingService;
    }

    public function index(Request $request)
    {
        try {
            $pricings = $this->pricing_service->list()->get();
            $data = PricingResource::collection($pricings);
            return ApiHelper::validResponse("Pricings returned successfully", $data);
        } catch (Exception $th) {
            //throw $th;
            return ApiHelper::problemResponse($this->serverErrorMessage, ApiConstants::SERVER_ERR_CODE, $th);
        }
    }

    public function show($id)
    {
        try {
            $pricing = $this->pricing_service->getById($id);
            $data = PricingResource::make($pricing);
            return ApiHelper::validResponse("Pricing details returned successfully", $data);
        } catch (Exception $th) {
            return ApiHelper::problemResponse($this->serverErrorMessage, ApiConstants::SERVER_ERR_CODE, $th);
        }
    }

    public function store(Request $request)
    {
        try {
            $pricing = $this->pricing_service->create($request->all());
            $data = PricingResource::make($pricing);
            return ApiHelper::validResponse("Pricing created successfully", $data);
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
            $pricing = $this->pricing_service->update($request->all(), $id);
            $data = PricingResource::make($pricing);
            return ApiHelper::validResponse("Pricing updated successfully", $data);
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
            $pricing = $this->pricing_service->getById($id);
            $pricing->delete();
            return ApiHelper::validResponse("Pricing deleted successfully");
        } catch (ModelNotFoundException $th) {
            return ApiHelper::problemResponse($th->getMessage(), ApiConstants::BAD_REQ_ERR_CODE, $th);
        } catch (Exception $th) {
            return ApiHelper::problemResponse($this->serverErrorMessage, ApiConstants::SERVER_ERR_CODE, $th);
        }
    }
}
