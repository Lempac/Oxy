<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::first();
$server = App\Models\Server::first();

if($user && $server) {
    setPermissionsTeamId($server->id);
    echo "User roles in server {$server->id}: \n";
    foreach($user->roles()->get() as $r) {
        echo "- {$r->name} \n";
    }

    echo "\nHas CAN_CREATE_CHANNEL? " . ($user->hasPermissionTo('CAN_CREATE_CHANNEL') ? 'yes' : 'no') . "\n";
} else {
    echo "No users or servers found\n";
}
