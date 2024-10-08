<?php

namespace App\Http\Resources\Finance\Subscription;

use App\Http\Resources\Finance\Plan\PlanResource;
use App\Http\Resources\User\UserResource;
use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            "price" => $this->price,
            "paid_on" => $this->paid_on,
            "expires_at" => $this->expires_at,
            "status" => $this->status,
            'user' => UserResource::make($this->whenLoaded("user", $this->user)),
            "plan" => PlanResource::make($this->whenLoaded("plan", $this->plan)),
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at
        ];
    }

    public static function customSubscription($model)
    {
        return [
            'id' => $model->id,
            "plan" => PlanResource::make($model->plan),
            "status" => $model->status,
            "created_at" => $model->created_at,
            "updated_at" => $model->updated_at
        ];
    }
}
