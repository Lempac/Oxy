<?php

namespace Database\Seeders;

use App\Enums\ChannelType;
use App\Enums\Theme;
use App\Enums\UserStatus;
use App\Enums\WhiteboardSyncState;
use App\Models\Board;
use App\Models\Call;
use App\Models\Channel;
use App\Models\Message;
use App\Models\Note;
use App\Models\Role;
use App\Models\Server;
use App\Models\ServerInvite;
use App\Models\User;
use App\Models\Whiteboard;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with deterministic test fixtures.
     */
    public function run(): void
    {
        // 1. Ensure all system permissions exist
        $this->seedPermissions();

        // 2. Create core test users
        $testUser = User::firstOrCreate([
            'nickname' => 'testuser',
        ], [
            'password' => Hash::make('password'),
            'status' => UserStatus::Online->value,
            'about_me' => 'Hey there! I am the administrator of Oxy.',
            'light_theme' => Theme::Oxy->value,
            'dark_theme' => Theme::Night->value,
        ]);

        $moderatorUser = User::firstOrCreate([
            'nickname' => 'moderator',
        ], [
            'password' => Hash::make('password'),
            'status' => UserStatus::Online->value,
            'about_me' => 'Community moderator keeping the servers clean and friendly.',
            'light_theme' => Theme::Oxy->value,
            'dark_theme' => Theme::Dracula->value,
        ]);

        $memberUser = User::firstOrCreate([
            'nickname' => 'member',
        ], [
            'password' => Hash::make('password'),
            'status' => UserStatus::Idle->value,
            'about_me' => 'Just another Oxy user exploring voice and whiteboard channels.',
            'light_theme' => Theme::Oxy->value,
            'dark_theme' => Theme::Synthwave->value,
        ]);

        // 3. Seed Server 1: Oxy HQ (testuser = Owner, moderator = Moderator, member = Member)
        $this->seedOxyHqServer($testUser, $moderatorUser, $memberUser);

        // 4. Seed Server 2: Community Hangout (moderator = Owner, testuser = Member, member = Moderator)
        $this->seedCommunityServer($testUser, $moderatorUser, $memberUser);
    }

    private function seedPermissions(): void
    {
        $permissions = [
            'CAN_DELETE_SERVER' => ['title' => 'Delete Server', 'description' => 'Delete the entire server', 'category' => 'Server Settings'],
            'CAN_EDIT_SERVER' => ['title' => 'Edit Server', 'description' => 'Change server name, icon, and settings', 'category' => 'Server Settings'],
            'CAN_MANAGE_SERVER' => ['title' => 'Manage Server', 'description' => 'Full administrative access to the server', 'category' => 'Server Settings'],
            'CAN_CREATE_CHANNEL' => ['title' => 'Create Channels', 'description' => 'Create new text or voice channels', 'category' => 'Channel Settings'],
            'CAN_DELETE_CHANNEL' => ['title' => 'Delete Channels', 'description' => 'Delete existing channels', 'category' => 'Channel Settings'],
            'CAN_EDIT_CHANNEL' => ['title' => 'Edit Channels', 'description' => 'Modify channel settings and names', 'category' => 'Channel Settings'],
            'CAN_MANAGE_CHANNEL' => ['title' => 'Manage Channels', 'description' => 'Full control over channel settings', 'category' => 'Channel Settings'],
            'CAN_SEE_CHANNEL' => ['title' => 'View Channels', 'description' => 'See and read channels', 'category' => 'Channel Settings'],
            'CAN_CREATE_MESSAGE' => ['title' => 'Send Messages', 'description' => 'Send messages in text channels', 'category' => 'Message Settings'],
            'CAM_CREATE_ATTACHMENTS' => ['title' => 'Attach Files', 'description' => 'Upload images and files to channels', 'category' => 'Message Settings'],
            'CAN_DELETE_MESSAGE' => ['title' => 'Manage Messages', 'description' => 'Delete messages sent by other users', 'category' => 'Message Settings'],
            'CAN_CREATE_ROLE' => ['title' => 'Create Roles', 'description' => 'Create new server roles', 'category' => 'Role Settings'],
            'CAN_DELETE_ROLE' => ['title' => 'Delete Roles', 'description' => 'Delete server roles', 'category' => 'Role Settings'],
            'CAN_EDIT_ROLE' => ['title' => 'Edit Roles', 'description' => 'Modify role settings and permissions', 'category' => 'Role Settings'],
            'CAN_MANAGE_ROLE' => ['title' => 'Manage Roles', 'description' => 'Full control over server roles', 'category' => 'Role Settings'],
            'CAN_MANAGE_MEMBERS' => ['title' => 'Manage Members', 'description' => 'Change member nicknames and settings', 'category' => 'Member Settings'],
            'CAN_INVITE' => ['title' => 'Create Invites', 'description' => 'Invite new members to the server', 'category' => 'Member Settings'],
            'CAN_KICK' => ['title' => 'Kick Members', 'description' => 'Remove members from the server', 'category' => 'Member Settings'],
            'CAN_EDIT_MEMBER_ROLES' => ['title' => 'Manage Member Roles', 'description' => 'Assign or remove roles from members', 'category' => 'Member Settings'],
        ];

        foreach ($permissions as $name => $data) {
            Permission::firstOrCreate(
                ['name' => $name, 'guard_name' => 'web'],
                $data
            );
        }
    }

    private function seedOxyHqServer(User $owner, User $moderator, User $member): void
    {
        $server = Server::firstOrCreate([
            'name' => 'Oxy HQ',
        ], [
            'description' => 'Official Oxy development & preview headquarters.',
            'slug' => 'oxy-hq',
            'enable_whiteboard' => true,
        ]);

        // Server Roles
        setPermissionsTeamId($server->id);

        $ownerRole = Role::firstOrCreate([
            'name' => 'Owner',
            'server_id' => $server->id,
            'guard_name' => 'web',
        ], [
            'color' => '#150f83',
            'importance' => 0,
        ]);
        $ownerRole->syncPermissions(Permission::pluck('name')->toArray());

        $modRole = Role::firstOrCreate([
            'name' => 'Moderator',
            'server_id' => $server->id,
            'guard_name' => 'web',
        ], [
            'color' => '#10b981',
            'importance' => 10,
        ]);
        $modRole->syncPermissions([
            'CAN_EDIT_SERVER',
            'CAN_CREATE_CHANNEL',
            'CAN_EDIT_CHANNEL',
            'CAN_DELETE_CHANNEL',
            'CAN_MANAGE_CHANNEL',
            'CAN_SEE_CHANNEL',
            'CAN_CREATE_MESSAGE',
            'CAM_CREATE_ATTACHMENTS',
            'CAN_DELETE_MESSAGE',
            'CAN_MANAGE_MEMBERS',
            'CAN_INVITE',
            'CAN_KICK',
        ]);

        $memberRole = Role::firstOrCreate([
            'name' => 'Member',
            'server_id' => $server->id,
            'guard_name' => 'web',
        ], [
            'color' => '#64748b',
            'importance' => 100,
        ]);
        $memberRole->syncPermissions([
            'CAN_SEE_CHANNEL',
            'CAN_CREATE_MESSAGE',
            'CAM_CREATE_ATTACHMENTS',
            'CAN_INVITE',
        ]);

        $server->update(['default_role_id' => $memberRole->id]);

        // Attach users and assign roles
        $this->attachUserWithRole($server, $owner, $ownerRole);
        $this->attachUserWithRole($server, $moderator, $modRole);
        $this->attachUserWithRole($server, $member, $memberRole);

        // Channels
        $announcements = Channel::firstOrCreate([
            'server_id' => $server->id,
            'slug' => 'announcements',
        ], [
            'name' => 'announcements',
            'type' => ChannelType::Text,
        ]);

        $general = Channel::firstOrCreate([
            'server_id' => $server->id,
            'slug' => 'general',
        ], [
            'name' => 'general',
            'type' => ChannelType::Text,
        ]);

        $random = Channel::firstOrCreate([
            'server_id' => $server->id,
            'slug' => 'random',
        ], [
            'name' => 'random',
            'type' => ChannelType::Text,
        ]);

        $voiceLounge = Channel::firstOrCreate([
            'server_id' => $server->id,
            'slug' => 'community-lounge',
        ], [
            'name' => 'Community Lounge',
            'type' => ChannelType::Voice,
        ]);
        Call::firstOrCreate(['channel_id' => $voiceLounge->id]);

        $whiteboard = Channel::firstOrCreate([
            'server_id' => $server->id,
            'slug' => 'project-canvas',
        ], [
            'name' => 'Project Canvas',
            'type' => ChannelType::Whiteboard,
        ]);
        Whiteboard::firstOrCreate([
            'channel_id' => $whiteboard->id,
        ], [
            'sync_status' => WhiteboardSyncState::Synced,
            'state' => '{"elements":[],"appState":{"viewBackgroundColor":"#ffffff"}}',
        ]);

        // Seed rich messages
        if ($announcements->messages()->count() === 0) {
            Message::create([
                'channel_id' => $announcements->id,
                'user_id' => $owner->id,
                'content' => '🎉 Welcome to Oxy! This preview environment is fully functional with live Reverb WebSockets, Yjs whiteboards, and SQLite persistence.',
            ]);
        }

        if ($general->messages()->count() === 0) {
            Message::create([
                'channel_id' => $general->id,
                'user_id' => $owner->id,
                'content' => 'Hey everyone, welcome to the Oxy HQ general channel!',
            ]);
            Message::create([
                'channel_id' => $general->id,
                'user_id' => $moderator->id,
                'content' => 'Real-time WebSocket events and channel switching are working smoothly.',
            ]);
            Message::create([
                'channel_id' => $general->id,
                'user_id' => $member->id,
                'content' => 'Loving the new UI themes and instant preview deployments!',
            ]);
            Message::create([
                'channel_id' => $general->id,
                'user_id' => $owner->id,
                'content' => 'Be sure to try out the #project-canvas whiteboard and invite friends with code OXY-PREVIEW.',
            ]);
        }

        if ($random->messages()->count() === 0) {
            Message::create([
                'channel_id' => $random->id,
                'user_id' => $member->id,
                'content' => 'Anyone ready for a voice call in the Community Lounge?',
            ]);
            Message::create([
                'channel_id' => $random->id,
                'user_id' => $moderator->id,
                'content' => 'Hopping in now! 🎧',
            ]);
        }

        // Kanban Board & Notes
        $board = Board::firstOrCreate([
            'server_id' => $server->id,
        ], [
            'name' => 'Oxy Roadmap',
        ]);

        Note::firstOrCreate([
            'board_id' => $board->id,
            'title' => 'Preview CI Deployments',
        ], [
            'text' => 'Automatic PR deployments on Raspberry Pi via Uncloud with ephemeral databases and volume cleanup.',
        ]);

        Note::firstOrCreate([
            'board_id' => $board->id,
            'title' => 'Realtime Whiteboarding',
        ], [
            'text' => 'Collaborative canvas whiteboard powered by Yjs WebSocket server with instant sync.',
        ]);

        // Server Invite
        ServerInvite::firstOrCreate([
            'code' => 'OXY-PREVIEW',
        ], [
            'server_id' => $server->id,
            'created_by_user_id' => $owner->id,
            'max_uses' => null,
            'uses' => 0,
            'expires_at' => null,
        ]);
    }

    private function seedCommunityServer(User $testUser, User $owner, User $member): void
    {
        $server = Server::firstOrCreate([
            'name' => 'Community Hangout',
        ], [
            'description' => 'An open community hub for conversations, testing, and brainstorming.',
            'slug' => 'community-hangout',
            'enable_whiteboard' => true,
        ]);

        // Server Roles
        setPermissionsTeamId($server->id);

        $ownerRole = Role::firstOrCreate([
            'name' => 'Owner',
            'server_id' => $server->id,
            'guard_name' => 'web',
        ], [
            'color' => '#8b5cf6',
            'importance' => 0,
        ]);
        $ownerRole->syncPermissions(Permission::pluck('name')->toArray());

        $modRole = Role::firstOrCreate([
            'name' => 'Moderator',
            'server_id' => $server->id,
            'guard_name' => 'web',
        ], [
            'color' => '#06b6d4',
            'importance' => 10,
        ]);
        $modRole->syncPermissions([
            'CAN_EDIT_SERVER',
            'CAN_CREATE_CHANNEL',
            'CAN_EDIT_CHANNEL',
            'CAN_DELETE_CHANNEL',
            'CAN_MANAGE_CHANNEL',
            'CAN_SEE_CHANNEL',
            'CAN_CREATE_MESSAGE',
            'CAM_CREATE_ATTACHMENTS',
            'CAN_DELETE_MESSAGE',
            'CAN_MANAGE_MEMBERS',
            'CAN_INVITE',
            'CAN_KICK',
        ]);

        $memberRole = Role::firstOrCreate([
            'name' => 'Member',
            'server_id' => $server->id,
            'guard_name' => 'web',
        ], [
            'color' => '#64748b',
            'importance' => 100,
        ]);
        $memberRole->syncPermissions([
            'CAN_SEE_CHANNEL',
            'CAN_CREATE_MESSAGE',
            'CAM_CREATE_ATTACHMENTS',
            'CAN_INVITE',
        ]);

        $server->update(['default_role_id' => $memberRole->id]);

        // In this server: moderator is Owner, member is Moderator, testuser is Member
        $this->attachUserWithRole($server, $owner, $ownerRole);
        $this->attachUserWithRole($server, $member, $modRole);
        $this->attachUserWithRole($server, $testUser, $memberRole);

        // Channels
        $welcomeChannel = Channel::firstOrCreate([
            'server_id' => $server->id,
            'slug' => 'welcome',
        ], [
            'name' => 'welcome',
            'type' => ChannelType::Text,
        ]);

        $chatChannel = Channel::firstOrCreate([
            'server_id' => $server->id,
            'slug' => 'chat',
        ], [
            'name' => 'chat',
            'type' => ChannelType::Text,
        ]);

        $voiceChannel = Channel::firstOrCreate([
            'server_id' => $server->id,
            'slug' => 'hangout-voice',
        ], [
            'name' => 'Hangout Voice',
            'type' => ChannelType::Voice,
        ]);
        Call::firstOrCreate(['channel_id' => $voiceChannel->id]);

        $whiteboard = Channel::firstOrCreate([
            'server_id' => $server->id,
            'slug' => 'sketchpad',
        ], [
            'name' => 'Sketchpad',
            'type' => ChannelType::Whiteboard,
        ]);
        Whiteboard::firstOrCreate([
            'channel_id' => $whiteboard->id,
        ], [
            'sync_status' => WhiteboardSyncState::Synced,
            'state' => '{"elements":[],"appState":{"viewBackgroundColor":"#ffffff"}}',
        ]);

        // Messages
        if ($welcomeChannel->messages()->count() === 0) {
            Message::create([
                'channel_id' => $welcomeChannel->id,
                'user_id' => $owner->id,
                'content' => 'Welcome to Community Hangout! Feel free to hang out and share ideas.',
            ]);
        }

        if ($chatChannel->messages()->count() === 0) {
            Message::create([
                'channel_id' => $chatChannel->id,
                'user_id' => $member->id,
                'content' => 'Hey everyone! Excited for the next Oxy feature drop.',
            ]);
            Message::create([
                'channel_id' => $chatChannel->id,
                'user_id' => $testUser->id,
                'content' => 'Hello from testuser! Testing standard member role permissions on this server.',
            ]);
        }

        // Server Invite
        ServerInvite::firstOrCreate([
            'code' => 'OXY-COMMUNITY',
        ], [
            'server_id' => $server->id,
            'created_by_user_id' => $owner->id,
            'max_uses' => null,
            'uses' => 0,
            'expires_at' => null,
        ]);
    }

    private function attachUserWithRole(Server $server, User $user, Role $role): void
    {
        if (! $server->users()->where('users.id', $user->id)->exists()) {
            $server->users()->attach($user->id);
        }

        setPermissionsTeamId($server->id);
        if (! $user->hasRole($role->name)) {
            $user->assignRole($role);
        }
    }
}
