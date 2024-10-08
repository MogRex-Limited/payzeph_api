<?php

namespace App\Http\Resources\Finance\Pricing;

use Illuminate\Http\Resources\Json\JsonResource;

class PricingResource extends JsonResource
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
            "id" => $this->id,
            "minimum" => $this->minimum,
            "maximum" => $this->maximum,
            "amount" => $this->amount,
            "status" => $this->status,
            "created_at" => $this->created_at
        ];
    }
}
