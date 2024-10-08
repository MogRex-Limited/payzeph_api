<?php

use App\Constants\Finance\CurrencyConstants;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCurrenciesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('currencies', function (Blueprint $table) {
            $table->id();
            $table->string("name");
            $table->enum("group" , [
                CurrencyConstants::CURRENCY_GROUP,
                CurrencyConstants::COIN_GROUP,
            ]);
            $table->string("type", 50)->unique();
            $table->double("price_per_dollar");
            $table->string("short_name", 20)->unique();
            $table->string("logo");
            $table->string("status");
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('currencies');
    }
}
