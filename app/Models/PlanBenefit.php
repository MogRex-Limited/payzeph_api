<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlanBenefit extends Model
{
    use HasFactory;

    protected $guarded = ["id"];

    public function plan()
    {
        return $this->belongsTo(Plan::class, "plan_id", "id");
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, "currency_id");
    }

    public function getTitle()
    {
        return str_replace(["{{level}}", "{{value}}"], $this->level, $this->title);
    }


    public function getDescription()
    {
        $value = $this->value;
        // if($this->value_type == TransactionConstants::PERCENTAGE_VALUE){
        //     $value.="%";
        // }
        return str_replace(["{{level}}", "{{value}}", "{{currency_type}}"], [$this->level, $value, optional($this->currency)->name], $this->description);
    }
}
