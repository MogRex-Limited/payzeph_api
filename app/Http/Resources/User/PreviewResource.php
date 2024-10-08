<?php

namespace App\Http\Resources\User;

use App\Http\Resources\Coperate\Group\GroupResource;
use App\Http\Resources\Coperate\Parish\ParishResource;
use Illuminate\Http\Resources\Json\JsonResource;

class PreviewResource extends JsonResource
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
            "parishes" => ParishResource::collection($this->parishes()),
            "groups" => GroupResource::collection($this->groups()),
        ];
    }
}
