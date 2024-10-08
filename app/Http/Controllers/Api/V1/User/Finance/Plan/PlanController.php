<?php

namespace App\Http\Controllers\Api\V1\User\Finance\Plan;

use App\Constants\General\ApiConstants;
use App\Exceptions\General\ModelNotFoundException;
use App\Helpers\ApiHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\Finance\Plan\PlanResource;
use App\Services\Finance\Plan\PlanService;
use Exception;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    protected $plan_service;

    function __construct()
    {
        $this->plan_service = new PlanService;
    }
    public function index(Request $request)
    {
        try {
            $plans = $this->plan_service->list()->get();
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
}
