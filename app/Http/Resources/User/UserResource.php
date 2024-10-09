<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            "first_name" => $this->first_name,
            "middle_name" => $this->middle_name,
            "last_name" => $this->last_name,
            "email" => $this->email,
            "zeph_id" => $this->zeph_id,
            "status" => $this->status,
            "phone_number" => $this->phone_number,
            "business_name" => $this->business_name,
            "business_category" => $this->business_category,
            "email_verified_at" => $this->email_verified_at,
            "created_at" => $this->created_at,
        ];
    }
}
