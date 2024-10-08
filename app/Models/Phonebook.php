<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Phonebook extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function phonebookGroup()
    {
        return $this->belongsTo(PhonebookGroup::class, "phonebook_group_id");
    }
}
