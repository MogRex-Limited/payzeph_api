<?php

namespace App\Http\Resources\Provider;

use Illuminate\Http\Resources\Json\JsonResource;

class ProviderRouteResource extends JsonResource
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
            "is_default" => $this->is_default,
            "status" => $this->status,
            "provider" => ProviderResource::make($this->whenLoaded("provider", $this->provider)),
            "created_at" => $this->created_at
        ];
    }
}
