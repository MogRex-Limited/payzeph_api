<?php

namespace App\Http\Controllers\Api\V1\User\Finance\Pricing;

use App\Constants\General\ApiConstants;
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

    public function calculate(Request $request)
    {
        try {
            $response = $this->pricing_service->calculate($request->all());
            return ApiHelper::validResponse("Pricing details returned successfully", $response);
        } catch (ValidationException $e) {
            return ApiHelper::inputErrorResponse($this->validationErrorMessage, ApiConstants::VALIDATION_ERR_CODE, $e);
        } catch (Exception $th) {
            return ApiHelper::problemResponse($this->serverErrorMessage, ApiConstants::SERVER_ERR_CODE, $th);
        }
    }
}
