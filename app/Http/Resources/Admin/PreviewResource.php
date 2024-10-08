<?php

namespace App\Http\Resources\Admin;

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
            "id" => (int) $this->id,
            "first_name" => $this->first_name,
            "email" => $this->email,
            "phone_number" => $this->phone_number,
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at
        ];
    }
}
