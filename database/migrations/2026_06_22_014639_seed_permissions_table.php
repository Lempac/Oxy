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
            'CAN_DELETE_SERVER',
            'CAN_EDIT_SERVER',
            'CAN_CREATE_CHANNEL',
            'CAN_DELETE_CHANNEL',
            'CAN_EDIT_CHANNEL',
            'CAN_CREATE_MESSAGE',
            'CAM_CREATE_ATTACHMENTS',
            'CAN_DELETE_MESSAGE',
            'CAN_MANAGE_CHANNEL',
            'CAN_CREATE_ROLE',
            'CAN_DELETE_ROLE',
            'CAN_EDIT_ROLE',
            'CAN_MANAGE_MEMBERS',
            'CAN_MANAGE_ROLE',
            'CAN_MANAGE_SERVER',
            'CAN_SEE_CHANNEL',
            'CAN_INVITE',
            'CAN_KICK',
            'CAN_EDIT_MEMBER_ROLES',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Depending on your setup, you might want to delete them or leave them
    }
};
