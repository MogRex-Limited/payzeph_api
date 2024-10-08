<?php

namespace App\Http\Resources\Phonebook;

use App\Http\Resources\User\UserResource;
use Illuminate\Http\Resources\Json\JsonResource;

class PhonebookResource extends JsonResource
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
            "number" => $this->number,
            "status" => $this->status,
            "phonebook_group" => PhonebookGroupResource::make($this->whenLoaded("phonebookGroup", $this->phonebookGroup)),
            "created_at" => $this->created_at,
        ];
    }
}
