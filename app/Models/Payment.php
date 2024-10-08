<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class, "transaction_id", "id");
    }

    public function user()
    {
        return $this->belongsTo(User::class, "user_id", "id");
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class, "plan_id", "id");
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, "currency_id", "id");
    }

    public function scopeSearch($query, $value)
    {
        $query->whereRaw("CONCAT(reference) LIKE ?", ["%$value%"])
            ->orwhereHas('user', function ($query) use ($value) {
                $query->search($value);
            });
    }

    public function scopeToday($query)
    {
        return $query->whereBetween('created_at', [now()->startOfDay(), now()->endOfDay()]);
    }
}
