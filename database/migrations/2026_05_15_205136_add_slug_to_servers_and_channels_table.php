<?php

use App\Models\Channel;
use App\Models\Server;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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

        // Backfill slugs for existing servers
        $servers = Server::all();
        foreach ($servers as $server) {
            $slug = Str::slug($server->name);
            $originalSlug = $slug;
            $count = 1;
            while (Server::where('slug', $slug)->exists()) {
                $slug = "{$originalSlug}-{$count}";
                $count++;
            }
            $server->slug = $slug;
            $server->save();
        }

        Schema::table('servers', function (Blueprint $table) {
            $table->string('slug')->nullable(false)->change();
        });

        Schema::table('channels', function (Blueprint $table) {
            $table->string('slug')->nullable(); // scoped unique by server later if needed
        });

        // Backfill slugs for existing channels
        $channels = Channel::all();
        foreach ($channels as $channel) {
            $slug = Str::slug($channel->name);
            $originalSlug = $slug;
            $count = 1;
            while (Channel::where('server_id', $channel->server_id)->where('slug', $slug)->exists()) {
                $slug = "{$originalSlug}-{$count}";
                $count++;
            }
            $channel->slug = $slug;
            $channel->save();
        }

        Schema::table('channels', function (Blueprint $table) {
            $table->unique(['server_id', 'slug']);
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
