<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankPaymentProof extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class , "user_id");
    }

    public function proof()
    {
        return $this->belongsTo(File::class , "file_id");
    }

    public function approvedBy()
    {
        return $this->belongsTo(Admin::class , "approved_by");
    }
}
