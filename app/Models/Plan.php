<?php

namespace App\Models;

use App\Constants\General\StatusConstants;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;

    protected $guarded = [];


    public function user()
    {
        return $this->belongsTo(User::class, "user_id");
    }

    public function logo()
    {
        return $this->belongsTo(File::class, "logo_id");
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, "currency_id");
    }

    public function benefits()
    {
        return $this->hasMany(PlanBenefit::class, "plan_id");
    }

    public function scopeStatus($query, $status = StatusConstants::ACTIVE)
    {
        return $query->where("status", $status);
    }
}
