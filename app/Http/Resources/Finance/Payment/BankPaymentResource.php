<?php

namespace App\Http\Resources\Finance\Payment;

use App\Http\Resources\General\FileResource;
use App\Http\Resources\User\UserResource;
use Illuminate\Http\Resources\Json\JsonResource;

class BankPaymentResource extends JsonResource
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
            "user" => UserResource::make($this->whenLoaded("user", $this->user)),
            "file" => FileResource::make($this->whenLoaded("proof", $this->proof)),
            "type" => $this->type,
            "amount" => $this->amount,
            "description" => $this->description,
            "reference" => $this->reference,
            "status" => $this->status,
            "approved_at" => $this->approved_at,
            "created_at" => $this->created_at
        ];
    }
}
