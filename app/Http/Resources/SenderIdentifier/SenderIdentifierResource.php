<?php

namespace App\Http\Resources\SenderIdentifier;

use App\Http\Resources\User\UserResource;
use Illuminate\Http\Resources\Json\JsonResource;

class SenderIdentifierResource extends JsonResource
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
            "identifier" => $this->identifier,
            "is_default" => $this->is_default,
            "user" => UserResource::make($this->whenLoaded("user", $this->user)),
            "created_at" => $this->created_at,
            "status" => $this->status,
        ];
    }
}
