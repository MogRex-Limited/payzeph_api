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
        Schema::create('phonebooks', function (Blueprint $table) {
            $table->id();
            $table->foreignId("phonebook_group_id")->constrained("phonebook_groups")->cascadeOnDelete();
            $table->string("name")->nullable();
            $table->string("number");
            $table->string("status")->nullable()->default(StatusConstants::ACTIVE);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('phonebooks');
    }
};
