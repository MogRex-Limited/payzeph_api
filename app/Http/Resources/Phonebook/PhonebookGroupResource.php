<?php

namespace App\Http\Resources\Phonebook;

use Illuminate\Http\Resources\Json\JsonResource;

class PhonebookGroupResource extends JsonResource
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
            "identifier" => $this->identifier,
            "status" => $this->status,
            "created_at" => $this->created_at,
        ];
    }
}
