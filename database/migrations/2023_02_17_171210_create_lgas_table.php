<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLgasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lgas', function (Blueprint $table) {
            $table->id();
            $table->string("name");
            $table->foreignId('state_id')->constrained("states")->cascadeOnDelete();
            $table->foreignId('country_id')->constrained("countries")->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('lgas');
    }
}
