<?php

namespace App\Models;

use App\Constants\General\StatusConstants;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subscription extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class, "user_id");
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class, "plan_id");
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, "currency_id");
    }

    public function scopeToday($query)
    {
        $start_time = Carbon::now()->startOfDay();
        $end_time = $start_time->copy()->endOfDay();

        return $query->whereBetween('created_at', [$start_time, $end_time]);
    }

    public function scopeStatus($query, $status = StatusConstants::ACTIVE)
    {
        return $query->where('status', $status);
    }

    public function scopeSearch($query, $key)
    {
        $query->where(function ($query) use ($key) {
            $query->whereHas("user", function ($user) use ($key) {
                $user->search($key);
            });
        });
    }
}
