<?php

use App\Constants\General\StatusConstants;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('provider_routes', function (Blueprint $table) {
            $table->id();
            $table->foreignId("provider_id")->constrained("providers")->cascadeOnDelete();
            $table->string("name");
            $table->string("description")->nullable();
            $table->boolean("is_default")->nullable()->default(false);
            $table->string("status")->nullable()->default(StatusConstants::ACTIVE);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('provider_routes');
    }
};
