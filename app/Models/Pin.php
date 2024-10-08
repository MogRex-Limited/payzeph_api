<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pin extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function model()
    {
        return $this->belongsTo($this->model_class, $this->classPath(), "id");
    }

    public function classPath()
    {
        if ($this->model_class instanceof Admin) {
            return "admin_id";
        } elseif ($this->model_class instanceof User) {
            return "user_id";
        }
    }
}
