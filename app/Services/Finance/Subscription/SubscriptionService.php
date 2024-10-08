<?php

namespace App\Services\Finance\Subscription;

use App\Constants\Finance\PaymentConstants;
use App\Constants\Finance\PlanConstants;
use App\Constants\General\StatusConstants;
use App\Constants\System\ModelConstants;
use App\Exceptions\Finance\Plan\SubscriptionException;
use App\Exceptions\General\ModelNotFoundException;
use App\Http\Resources\Finance\Subscription\SubscriptionResource;
use App\Models\Subscription;
use App\Services\Finance\Plan\PlanService;
use App\Services\Finance\Subscription\SubscriptionInitiationWithCardService;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SubscriptionService
{
    public $plan_service;
    public $parish_service;
    public function __construct()
    {
        $this->plan_service = new PlanService;
    }
    public static function getById($id): Subscription
    {
        $subscription = Subscription::find($id);
        if (empty($subscription)) {
            throw new ModelNotFoundException("Subscription not found");
        }
        return $subscription;
    }

    public static function subscribeToPlan($model, $plan, $type = null, $paid_on = null)
    {
        $subscription = Subscription::create([
            "user_id" => $model->id,
            "plan_id" => $plan->id,
            "currency_id" => $plan->currency_id,
            "price" => $plan->price,
            "paid_on" => $paid_on ?? now(),
            "expires_at" => now()->addDays($plan->duration),
            "type" => $type ?? PlanConstants::ONE_TIME,
            "status" => StatusConstants::ACTIVE
        ]);

        return $subscription;
    }

    public static function checkForSubscription($model)
    {
        $currentSubscription = self::currentUserSubscription($model);
        if (!empty($currentSubscription)) {
            throw new SubscriptionException("You are already subscribed to a plan");
        }
    }

    public static function currentUserSubscription(Model $model)
    {
        return Subscription::where("user_id", $model->id)
            ->where("expires_at", ">", now())
            ->whereHas("plan")
            ->with("plan")
            ->where("status", StatusConstants::ACTIVE)
            ->orderby("expires_at", "desc")
            ->first();
    }

    public static function listByCoperateModel(Model $model)
    {
        return Subscription::where(ModelConstants::parseModelKey($model), $model->id)
            ->with("plan")
            ->orderby("expires_at", "desc")
            ->get();
    }

    public function validate(array $data)
    {
        $validator = Validator::make($data, [
            "plan_id" => "required|exists:plans,id",
            "method" => "required|string|in:card,wallet,bank",
            "gateway" => "nullable|string|required_if:method,card|" . Rule::in(PaymentConstants::GATEWAYS),
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }

    public function initiate($data)
    {
        $data = $this->validate($data);

        $method = $data["method"];
        if ($method == "wallet") {
            return $this->subscribeFromWallet($data);
        } else {
            return $this->subscribeWithCard($data);
        }
    }

    public function subscribeFromWallet($data)
    {
        try {
            $plan = $this->plan_service->getById($data["plan_id"]);

            self::checkForSubscription(auth()->user());

            $service = new SubscriptionInitiationWithWalletService($plan);

            $service->setUser(auth()->user())
                ->setAmount($plan->price)
                ->setCurrency($plan->currency)
                ->setDescription("Subscription to {$plan->name}")
                ->setGateway($data["gateway"]);

            $initialization = $service->byGateway();
            return SubscriptionResource::make($initialization["subscription"]);
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function subscribeWithCard($data)
    {
        try {
            $plan = $this->plan_service->getById($data["plan_id"]);

            self::checkForSubscription(auth()->user());

            $service = new SubscriptionInitiationWithCardService($plan);
            $service->setUser(auth()->user())
                ->setAmount($plan->price)
                ->setCurrency($plan->currency)
                ->setDescription("Subscription to {$plan->name}")
                ->setGateway($data["gateway"]);

            $initialization = $service->byGateway();

            return [
                "link" => $initialization["link"]
            ];
        } catch (Exception $e) {
            throw $e;
        }
    }
}
