<?php

namespace App\Http\Controllers\Api\V1\Admin\Finance\Plan;

use App\Constants\General\ApiConstants;
use App\Exceptions\General\ModelNotFoundException;
use App\Helpers\ApiHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\Finance\Plan\PlanBenefitResource;
use App\Models\PlanBenefit;
use App\Services\Finance\Plan\PlanBenefitService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PlanBenefitController extends Controller
{
    protected $plan_benefit_service;

    function __construct()
    {
        $this->plan_benefit_service = new PlanBenefitService;
    }
        public function index(Request $request, $plan_id)
    {
        try {
            $plan_benefits = PlanBenefit::with("plan")->where("plan_id", $plan_id)->get();
            $data = PlanBenefitResource::collection($plan_benefits);
            return ApiHelper::validResponse("Plan benefits returned successfully", $data);
        } catch (Exception $th) {
            //throw $th;
            return ApiHelper::problemResponse("Something went wrong while trying to process your request", ApiConstants::SERVER_ERR_CODE, $th);
        }
    }

    public function show($id)
    {
        try {
            $plan_benefit = $this->plan_benefit_service->getById($id);
            $data = PlanBenefitResource::make($plan_benefit);
            return ApiHelper::validResponse("Plan benefit details returned successfully", $data);
        } catch (ModelNotFoundException $th) {
            return ApiHelper::problemResponse($th->getMessage(), ApiConstants::BAD_REQ_ERR_CODE, $th);
        } catch (Exception $th) {
            return ApiHelper::problemResponse("Something went wrong while trying to process your request", ApiConstants::SERVER_ERR_CODE, $th);
        }
    }

    public function create(Request $request, $plan_id)
    {
        try {
            $response = $this->plan_benefit_service->save($request->all());
            $data = PlanBenefitResource::collection($response["plan"]->benefits);
            return ApiHelper::validResponse("Plan benefits created successfully", $data);
        } catch (ValidationException $th) {
            return ApiHelper::inputErrorResponse($this->validationErrorMessage, ApiConstants::VALIDATION_ERR_CODE, $th);
        } catch (ModelNotFoundException $th) {
            return ApiHelper::problemResponse($th->getMessage(), ApiConstants::BAD_REQ_ERR_CODE, $th);
        } catch (Exception $th) {
            return ApiHelper::problemResponse("Something went wrong while trying to process your request", ApiConstants::SERVER_ERR_CODE, $th);
        }
    }
}
