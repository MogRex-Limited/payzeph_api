<?php

namespace App\Http\Resources\Finance\Transaction;

use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
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
            'id' => $this->id,
            'type' => $this->type,
            'amount' => $this->amount,
            'unit_quantity' => $this->unit_quantity,
            'description' => $this->description,
            'status' => $this->status,
            'reference' => $this->reference,
            'batch_no' => $this->batch_no,
            'prev_balance' => $this->prev_balance,
            'current_balance' => $this->current_balance,
            'created_at' =>$this->created_at->format("Y-m-d H:i:s")
        ];
    }
}
