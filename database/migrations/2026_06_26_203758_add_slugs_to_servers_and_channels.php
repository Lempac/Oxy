<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('servers', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique();
        });

        Schema::table('channels', function (Blueprint $table) {
            $table->string('slug')->nullable();
            $table->unique(['server_id', 'slug']);
        });

        // Generate slugs for existing servers
        $servers = DB::table('servers')->get();
        foreach ($servers as $server) {
            $slug = Str::slug($server->name);
            $originalSlug = $slug;
            $count = 1;
            while (DB::table('servers')->where('slug', $slug)->exists()) {
                $slug = $originalSlug.'-'.$count;
                $count++;
            }
            DB::table('servers')->where('id', $server->id)->update(['slug' => $slug]);
        }

        // Generate slugs for existing channels
        $channels = DB::table('channels')->get();
        foreach ($channels as $channel) {
            $slug = Str::slug($channel->name);
            $originalSlug = $slug;
            $count = 1;
            while (DB::table('channels')->where('server_id', $channel->server_id)->where('slug', $slug)->exists()) {
                $slug = $originalSlug.'-'.$count;
                $count++;
            }
            DB::table('channels')->where('id', $channel->id)->update(['slug' => $slug]);
        }

        // Make columns non-nullable after filling data
        Schema::table('servers', function (Blueprint $table) {
            $table->string('slug')->nullable(false)->change();
        });

        Schema::table('channels', function (Blueprint $table) {
            $table->string('slug')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('channels', function (Blueprint $table) {
            $table->dropUnique(['server_id', 'slug']);
            $table->dropColumn('slug');
        });

        Schema::table('servers', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
};
