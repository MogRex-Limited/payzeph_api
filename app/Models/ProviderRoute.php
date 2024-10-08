<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProviderRoute extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'is_default' => "boolean",
    ];
    
    public function provider()
    {
        return $this->belongsTo(Provider::class, "provider_id");
    }
}
