<?php

use App\Enums\ChannelType;
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
        Schema::create('channels', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->enum('type', array_column(ChannelType::cases(), 'value'));
            $table->integer('can_see_channel')->nullable();
            $table->integer('can_create_message')->nullable();
            $table->integer('can_delete_message')->nullable();
            $table->timestamps();
            $table->foreignUuid('server_id')->constrained()->cascadeOnDelete()->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('channels');
    }
};
