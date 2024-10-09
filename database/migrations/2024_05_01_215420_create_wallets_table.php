<?php

use App\Constants\General\StatusConstants;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWalletsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId("user_id")->constrained("users")->cascadeOnDelete();
            $table->foreignId("currency_id")->constrained("currencies")->cascadeOnDelete();
            $table->string("type"); // Fiat, Token
            $table->string("number");
            $table->double("balance")->default(0);
            $table->double("locked_balance")->default(0);
            $table->double("total_credit")->default(0);
            $table->double("total_debit")->default(0);
            $table->string("pin")->nullable();
            $table->string("status")->nullable()->default(StatusConstants::ACTIVE);
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
        Schema::dropIfExists('wallets');
    }
}
