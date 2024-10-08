<?php

namespace App\Http\Resources\Setting;

use App\Http\Resources\User\UserResource;
use Illuminate\Http\Resources\Json\JsonResource;

class ApiCredentialResource extends JsonResource
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
            "public_key" => $this->public_key,
            "private_key" => $this->private_key,
            "webhook_url" => $this->webhook_url,
            "callback_url" => $this->callback_url,
            "connection_status" => $this->connection_status,
            "last_connection" => $this->last_connection,
            "created_at" => $this->created_at,
            "status" => $this->status,
            "user" => UserResource::make($this->whenLoaded("user", $this->user)),
        ];
    }
}
