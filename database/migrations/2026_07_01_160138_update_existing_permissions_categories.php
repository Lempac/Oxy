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
        $categories = [
            'Server Settings' => [
                'CAN_DELETE_SERVER',
                'CAN_EDIT_SERVER',
                'CAN_MANAGE_SERVER',
            ],
            'Channel Settings' => [
                'CAN_CREATE_CHANNEL',
                'CAN_DELETE_CHANNEL',
                'CAN_EDIT_CHANNEL',
                'CAN_MANAGE_CHANNEL',
                'CAN_SEE_CHANNEL',
            ],
            'Message Settings' => [
                'CAN_CREATE_MESSAGE',
                'CAM_CREATE_ATTACHMENTS',
                'CAN_DELETE_MESSAGE',
            ],
            'Role Settings' => [
                'CAN_CREATE_ROLE',
                'CAN_DELETE_ROLE',
                'CAN_EDIT_ROLE',
                'CAN_MANAGE_ROLE',
            ],
            'Member Settings' => [
                'CAN_MANAGE_MEMBERS',
                'CAN_INVITE',
                'CAN_KICK',
                'CAN_EDIT_MEMBER_ROLES',
            ],
        ];

        foreach ($categories as $category => $permissions) {
            Permission::whereIn('name', $permissions)->update(['category' => $category]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Permission::query()->update(['category' => null]);
    }
};
