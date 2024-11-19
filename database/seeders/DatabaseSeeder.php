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
        $servers->each(function (Server $server) {
            $channels = Channel::factory(4)->for($server)->create();
            $channels->each(function (Channel $channel) {
                Call::factory(2)->for($channel)->create();
            });
        });

        User::factory(10)->hasAttached($servers->random(2))->create()->each(function (User $user) {
            $user->servers->each(function (Server $server) use ($user) {
                $server->channels->random(2)->each(function (Channel $channel) use ($user) {
                    Message::factory(10)->for($channel)->for($user)->create();
                });
            });
        });

        $testUser = User::factory()->hasAttached($servers->random(2))->create([
            'name' => 'Test User',
            'email' => 'test@test.test',
        ]);

        $testUser->servers->each(function (Server $server) use ($testUser) {
            $testUser->roles()->attach($server->roles()->first());
            $server->channels->random(2)->each(function (Channel $channel) use ($testUser) {
                Message::factory(10)->for($channel)->for($testUser)->create();
            });
        });

    }
}
