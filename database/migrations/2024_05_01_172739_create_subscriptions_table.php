<?php

use App\Constants\Finance\PlanConstants;
use App\Constants\General\StatusConstants;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubscriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained("users")->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained("plans")->cascadeOnDelete();
            $table->foreignId("currency_id")->nullable()->constrained("currencies")->nullOnDelete();
            $table->double('price');
            $table->string('type')->nullable()->default(PlanConstants::ONE_TIME);
            $table->dateTime('paid_on')->nullable();
            $table->dateTime('expires_at')->nullable();
            $table->longText("history")->nullable();
            $table->string('status')->default(StatusConstants::PENDING);
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
        Schema::dropIfExists('subscriptions');
    }
}
