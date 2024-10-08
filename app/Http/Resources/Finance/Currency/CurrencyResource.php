<?php

namespace App\Http\Resources\Finance\Currency;

use Illuminate\Http\Resources\Json\JsonResource;

class CurrencyResource extends JsonResource
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
            "type" => $this->type,
            "short_name" => $this->short_name,
            "symbol" => $this->logo,
            "status" => $this->status,
            "created_at" => $this->created_at
        ];
    }
}
