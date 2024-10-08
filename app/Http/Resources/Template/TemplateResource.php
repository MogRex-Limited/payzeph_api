<?php

namespace App\Http\Resources\Template;

use App\Http\Resources\User\UserResource;
use Illuminate\Http\Resources\Json\JsonResource;

class TemplateResource extends JsonResource
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
            "id" => $this->uuid,
            "name" => $this->name,
            "type" => $this->type,
            "content" => $this->content,
            "dynamic_keys" => $this->extractContentInBrackets(),
            "user" => UserResource::make($this->whenLoaded("user", $this->user)),
            "created_at" => $this->created_at,
            "status" => $this->status,
        ];
    }
}
