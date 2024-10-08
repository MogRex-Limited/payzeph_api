<?php

namespace App\Http\Resources\Finance\Plan;

use App\Http\Resources\Coperate\Group\GroupResource;
use App\Http\Resources\Coperate\Parish\ParishResource;
use App\Http\Resources\Finance\Currency\CurrencyResource;
use App\Http\Resources\General\FileResource;
use App\Http\Resources\User\UserResource;
use Illuminate\Http\Resources\Json\JsonResource;

class PlanResource extends JsonResource
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
            "name" => $this->name,
            "description" => $this->description,
            "type" => $this->type,
            "frequency" => $this->frequency,
            "price" => $this->price,
            "duration" => $this->duration,
            "status" => $this->status,
            "logo" => FileResource::make($this->whenLoaded("logo", $this->logo)),
            "currency" => CurrencyResource::make($this->whenLoaded("currency", $this->currency)),
            "user" => UserResource::make($this->whenLoaded("user", $this->user)),
            "benefits" => PlanBenefitResource::collection($this->whenLoaded("benefits", $this->benefits)),
            "created_at" => $this->created_at
        ];
    }
}
