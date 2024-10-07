<?php

namespace Database\Seeders;

use App\Models\Call;
use App\Models\Channel;
use App\Models\Message;
use App\Models\Server;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $servers = Server::factory(4)->create();
        $servers->each(function ($server) {
            $channels = Channel::factory(4)->for($server)->create();
            $channels->each(function ($channel) {
                Call::factory(2)->for($channel)->create();
            });
        });
        User::factory(10)->hasAttached($servers->random(2))->create()->each(function ($user) {
            $user->servers->each(function ($server) use ($user) {
                $server->channels->random(2)->each(function ($channel) use ($user) {
                    Message::factory(10)->for($channel)->for($user)->create();
                });
            });
        });

//        User::factory()->create([
//            'name' => 'Test User',
//            'email' => 'test@example.com',
//        ]);
    }
}
