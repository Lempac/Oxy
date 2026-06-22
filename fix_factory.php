<?php

$content = file_get_contents('app/Models/Server.php');

$content = str_replace(
"        return ServerFactory::new()->hasRoles(1, [
            'name' => 'Owner',
            'color' => '#ffffff',
            'importance' => 0,
        ]);",
"        return ServerFactory::new()->afterCreating(function (\$server) {
            \$role = \\App\\Models\\Role::create([
                'name' => 'Owner',
                'color' => '#ffffff',
                'importance' => 0,
                'server_id' => \$server->id,
                'guard_name' => 'web',
            ]);

            \$permissions = \\Spatie\\Permission\\Models\\Permission::pluck('name')->toArray();
            \$role->syncPermissions(\$permissions);
        });",
$content);

file_put_contents('app/Models/Server.php', $content);
