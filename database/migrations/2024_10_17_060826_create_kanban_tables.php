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
        Schema::create('kanban_boards', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('bio')->nullable();
            $table->timestamps();
        });

        Schema::create('kanban_columns', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('kanban_board_id')
                  ->constrained('kanban_boards')
                  ->onDelete('cascade');
            $table->integer('position')->default(0);
            $table->timestamps();
        });

        Schema::create('kanban_tasks', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignId('kanban_column_id')
                  ->constrained('kanban_columns')
                  ->onDelete('cascade');
            $table->integer('position')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kanban_tasks');
        Schema::dropIfExists('kanban_columns');
        Schema::dropIfExists('kanban_boards');
    }
};
