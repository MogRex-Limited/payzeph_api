<?php

namespace App\Http\Controllers\Api\V1\User\Finance\Subscription;

use App\Constants\General\ApiConstants;
use App\Constants\General\StatusConstants;
use App\Exceptions\Finance\Payment\CatholicPayException;
use App\Exceptions\Finance\Plan\SubscriptionException;
use App\Exceptions\General\ModelNotFoundException;
use App\Helpers\ApiHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\Finance\Subscription\SubscriptionResource;
use App\Models\Subscription;
use App\Services\Finance\Subscription\SubscriptionService;
use Exception;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    protected $subscription_service;

    function __construct()
    {
        $this->subscription_service = new SubscriptionService;
    }
    public function index(Request $request)
    {
        try {
            $plans = Subscription::with("plan")->where("user_id", auth()->id())->get();
            $data = SubscriptionResource::collection($plans);
            return ApiHelper::validResponse("Subscriptions returned successfully", $data);
        } catch (Exception $th) {
            //throw $th;
            return ApiHelper::problemResponse($this->serverErrorMessage, ApiConstants::SERVER_ERR_CODE, $th);
        }
    }

    public function show($id)
    {
        try {
            $subscription = $this->subscription_service->getById($id);
            $data = SubscriptionResource::make($subscription);
            return ApiHelper::validResponse("Subscription details returned successfully", $data);
        } catch (ModelNotFoundException $th) {
            return ApiHelper::problemResponse($th->getMessage(), ApiConstants::BAD_REQ_ERR_CODE, $th);
        } catch (Exception $th) {
            return ApiHelper::problemResponse($this->serverErrorMessage, ApiConstants::SERVER_ERR_CODE, $th);
        }
    }

    public function cancel($id)
    {
        try {
            $subscription = $this->subscription_service->getById($id);
            $subscription->update([
                "status" => StatusConstants::CANCELLED
            ]);
            return ApiHelper::validResponse("Subsription cancelled successfully");
        } catch (ModelNotFoundException $th) {
            return ApiHelper::problemResponse($th->getMessage(), ApiConstants::BAD_REQ_ERR_CODE, $th);
        } catch (Exception $th) {
            return ApiHelper::problemResponse($this->serverErrorMessage, ApiConstants::SERVER_ERR_CODE, $th);
        }
    }

    public function subscribe(Request $request)
    {
        try {
            $response = $this->subscription_service->initiate($request->all());
            return ApiHelper::validResponse("Subscription initiated successfully", $response);
        } catch (SubscriptionException $th) {
            return ApiHelper::problemResponse($th->getMessage(), ApiConstants::BAD_REQ_ERR_CODE, $th);
        } catch (ModelNotFoundException $th) {
            return ApiHelper::problemResponse($th->getMessage(), ApiConstants::BAD_REQ_ERR_CODE, $th);
        } catch (CatholicPayException $th) {
            return ApiHelper::problemResponse($th->getMessage(), ApiConstants::BAD_REQ_ERR_CODE, $th);
        } catch (Exception $th) {
            return ApiHelper::problemResponse($this->serverErrorMessage, ApiConstants::SERVER_ERR_CODE, $th);
        }
    }
}
