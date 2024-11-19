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
            $table->id();
            $table->string('name');
            $table->enum('type', array_column(ChannelType::cases(), 'value'));
            $table->integer('can_see_channel')->nullable();
            $table->integer('can_create_message')->nullable();
            $table->integer('can_delete_message')->nullable();
            $table->timestamps();
            $table->unsignedBigInteger('server_id');
            $table->foreign('server_id')->cascadeOnUpdate()->cascadeOnDelete()->references('id')->on('servers');
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
