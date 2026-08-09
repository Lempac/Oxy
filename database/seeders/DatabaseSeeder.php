<?php

namespace Database\Seeders;

use App\Enums\UserStatus;
use App\Models\Call;
use App\Models\Channel;
use App\Models\Message;
use App\Models\Server;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

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

        $testUser = User::firstOrCreate([
            'nickname' => 'testuser',
        ], [
            'password' => Hash::make('password'),
            'status' => UserStatus::Online->value,
            'about_me' => 'Hey there! I am using Oxy.',
        ]);

        $testUser->servers->each(function (Server $server) use ($testUser) {
            setPermissionsTeamId($server->id);
            $testUser->assignRole($server->roles()->first());
            $server->channels->random(2)->each(function (Channel $channel) use ($testUser) {
                Message::factory(10)->for($channel)->for($testUser)->create();
            });
        });

    }
}
