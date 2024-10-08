<?php

namespace App\Http\Controllers\Api\V1\Admin\Finance\Subscription;

use App\Constants\General\ApiConstants;
use App\Exceptions\Coperate\ParishException;
use App\Exceptions\General\ModelNotFoundException;
use App\Helpers\ApiHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\Finance\Plan\PlanResource;
use App\Models\Plan;
use App\Services\Finance\Plan\PlanService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SubscriptionController extends Controller
{
    protected $plan_service;

    function __construct()
    {
        $this->plan_service = new PlanService;
    }
        public function index(Request $request)
    {
        try {
            $plans = Plan::with("benefits")->get();
            $data = PlanResource::collection($plans);
            return ApiHelper::validResponse("Plans returned successfully", $data);
        } catch (Exception $th) {
            //throw $th;
            return ApiHelper::problemResponse("Something went wrong while trying to process your request", ApiConstants::SERVER_ERR_CODE, $th);
        }
    }

    public function show($id)
    {
        try {
            $plan = $this->plan_service->getById($id);
            $data = PlanResource::make($plan);
            return ApiHelper::validResponse("Plan details returned successfully", $data);
        } catch (ModelNotFoundException $th) {
            return ApiHelper::problemResponse($th->getMessage(), ApiConstants::BAD_REQ_ERR_CODE, $th);
        } catch (Exception $th) {
            return ApiHelper::problemResponse("Something went wrong while trying to process your request", ApiConstants::SERVER_ERR_CODE, $th);
        }
    }

    public function create(Request $request)
    {
        try {
            $plan = $this->plan_service->create($request->all());
            $data = PlanResource::make($plan);
            return ApiHelper::validResponse("Plan created successfully", $data);
        } catch (ValidationException $th) {
            return ApiHelper::inputErrorResponse($this->validationErrorMessage, ApiConstants::VALIDATION_ERR_CODE, $th);
        } catch (ModelNotFoundException $th) {
            return ApiHelper::problemResponse($th->getMessage(), ApiConstants::BAD_REQ_ERR_CODE, $th);
        } catch (Exception $th) {
            return ApiHelper::problemResponse("Something went wrong while trying to process your request", ApiConstants::SERVER_ERR_CODE, $th);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $plan = $this->plan_service->update($request->all(), $id);
            $data = PlanResource::make($plan);
            return ApiHelper::validResponse("Plan updated successfully", $data);
        } catch (ValidationException $th) {
            return ApiHelper::inputErrorResponse($this->validationErrorMessage, ApiConstants::VALIDATION_ERR_CODE, $th);
        } catch (ModelNotFoundException $th) {
            return ApiHelper::problemResponse($th->getMessage(), ApiConstants::BAD_REQ_ERR_CODE, $th);
        } catch (Exception $th) {
            return ApiHelper::problemResponse("Something went wrong while trying to process your request", ApiConstants::SERVER_ERR_CODE, $th);
        }
    }

    public function destroy($id)
    {
        try {
            $plan = $this->plan_service->getById($id);
            $plan->delete();
            return ApiHelper::validResponse("Parish destroyed successfully");
        } catch (ModelNotFoundException $th) {
            return ApiHelper::problemResponse($th->getMessage(), ApiConstants::BAD_REQ_ERR_CODE, $th);
        } catch (Exception $th) {
            return ApiHelper::problemResponse("Something went wrong while trying to process your request", ApiConstants::SERVER_ERR_CODE, $th);
        }
    }
}
