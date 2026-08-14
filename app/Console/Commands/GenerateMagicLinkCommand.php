<?php

namespace App\Console\Commands;

use App\Enums\ChannelType;
use App\Enums\UserStatus;
use App\Models\Channel;
use App\Models\Message;
use App\Models\Role;
use App\Models\Server;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use Spatie\Permission\Models\Permission;

class GenerateMagicLinkCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auth:magic-link {nickname=testuser} {--days=7}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a 1-click temporary signed login URL for preview testing';

    public function handle(): int
    {
        $nickname = $this->argument('nickname');

        $user = User::firstOrCreate(
            ['nickname' => $nickname],
            [
                'password' => Hash::make('password'),
                'status' => UserStatus::Online->value,
                'about_me' => 'Preview testing account',
            ]
        );

        if (Server::count() === 0) {
            $server = Server::create([
                'name' => 'Demo Server',
                'description' => 'Preview environment test server',
                'enable_whiteboard' => true,
            ]);

            $ownerRole = Role::create([
                'name' => 'Owner',
                'color' => '#6366f1',
                'importance' => 0,
                'server_id' => $server->id,
                'guard_name' => 'web',
            ]);

            $permissions = Permission::pluck('name')->toArray();
            $ownerRole->syncPermissions($permissions);

            $user->servers()->attach($server->id);
            setPermissionsTeamId($server->id);
            $user->assignRole($ownerRole);

            $general = Channel::create([
                'server_id' => $server->id,
                'name' => 'general',
                'type' => ChannelType::Text->value,
                'position' => 0,
            ]);

            Message::create([
                'channel_id' => $general->id,
                'user_id' => $user->id,
                'content' => '🚀 Welcome to Oxy Preview! Real-time chat, voice, and whiteboard are ready for testing.',
            ]);

            Channel::create([
                'server_id' => $server->id,
                'name' => 'voice-lounge',
                'type' => ChannelType::Voice->value,
                'position' => 1,
            ]);

            Channel::create([
                'server_id' => $server->id,
                'name' => 'whiteboard',
                'type' => ChannelType::Whiteboard->value,
                'position' => 2,
            ]);
        }

        $days = (int) $this->option('days');
        $url = URL::temporarySignedRoute(
            'magic-login',
            now()->addDays($days),
            ['user' => $user->id]
        );

        $this->line($url);

        return self::SUCCESS;
    }
}
