<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Town extends Model
{
   use HasFactory;
   protected $guarded = [];

   public function lga()
   {
      return $this->belongsTo(Lga::class, "lga_id", "id");
   }

   public function state()
   {
      return $this->belongsTo(State::class, "state_id", "id");
   }

   public function country()
   {
      return $this->belongsTo(Country::class, "country_id", "id");
   }

   public function scopeSearch($query, $key)
   {
      return $query->where("name", "LIKE", "%$key%");
   }

   public function getFullNameAttribute()
   {
      if (!empty($this->lga)) {
         return $this->name . ", " . $this->lga->name;
      } else {
         return $this->name;
      }
   }
}
