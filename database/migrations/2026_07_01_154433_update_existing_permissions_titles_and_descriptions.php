<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $permissions = [
            'CAN_DELETE_SERVER' => ['title' => 'Delete Server', 'description' => 'Delete the entire server'],
            'CAN_EDIT_SERVER' => ['title' => 'Edit Server', 'description' => 'Change server name, icon, and settings'],
            'CAN_CREATE_CHANNEL' => ['title' => 'Create Channels', 'description' => 'Create new text or voice channels'],
            'CAN_DELETE_CHANNEL' => ['title' => 'Delete Channels', 'description' => 'Delete existing channels'],
            'CAN_EDIT_CHANNEL' => ['title' => 'Edit Channels', 'description' => 'Modify channel settings and names'],
            'CAN_CREATE_MESSAGE' => ['title' => 'Send Messages', 'description' => 'Send messages in text channels'],
            'CAM_CREATE_ATTACHMENTS' => ['title' => 'Attach Files', 'description' => 'Upload images and files to channels'],
            'CAN_DELETE_MESSAGE' => ['title' => 'Manage Messages', 'description' => 'Delete messages sent by other users'],
            'CAN_MANAGE_CHANNEL' => ['title' => 'Manage Channels', 'description' => 'Full control over channel settings'],
            'CAN_CREATE_ROLE' => ['title' => 'Create Roles', 'description' => 'Create new server roles'],
            'CAN_DELETE_ROLE' => ['title' => 'Delete Roles', 'description' => 'Delete server roles'],
            'CAN_EDIT_ROLE' => ['title' => 'Edit Roles', 'description' => 'Modify role settings and permissions'],
            'CAN_MANAGE_MEMBERS' => ['title' => 'Manage Members', 'description' => 'Change member nicknames and settings'],
            'CAN_MANAGE_ROLE' => ['title' => 'Manage Roles', 'description' => 'Full control over server roles'],
            'CAN_MANAGE_SERVER' => ['title' => 'Manage Server', 'description' => 'Full administrative access to the server'],
            'CAN_SEE_CHANNEL' => ['title' => 'View Channels', 'description' => 'See and read channels'],
            'CAN_INVITE' => ['title' => 'Create Invites', 'description' => 'Invite new members to the server'],
            'CAN_KICK' => ['title' => 'Kick Members', 'description' => 'Remove members from the server'],
            'CAN_EDIT_MEMBER_ROLES' => ['title' => 'Manage Member Roles', 'description' => 'Assign or remove roles from members'],
        ];

        foreach ($permissions as $name => $data) {
            Permission::where('name', $name)->update($data);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Permission::query()->update(['title' => null, 'description' => null]);
    }
};
