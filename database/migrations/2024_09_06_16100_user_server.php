<?php

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
        Schema::create('user_server', function (Blueprint $table) {
            $table->string('user_id');
            $table->unsignedBigInteger('server_id');


            $table->foreign('server_id')->references('id')->on('servers');
            $table->foreign('user_id')->references('email')->on('users');
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
