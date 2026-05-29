<?php

namespace App\Observers;

use App\Models\Server;
use Illuminate\Support\Str;

class ServerObserver
{
    public function creating(Server $server)
    {
        $this->generateSlug($server);
    }

    public function updating(Server $server)
    {
        if ($server->isDirty('name')) {
            $this->generateSlug($server);
        }
    }

    private function generateSlug(Server $server)
    {
        $slug = Str::slug($server->name);
        $originalSlug = $slug;
        $count = 1;

        while (Server::where('slug', $slug)->where('id', '!=', $server->id ?? 0)->exists()) {
            $slug = "{$originalSlug}-{$count}";
            $count++;
        }

        $server->slug = $slug;
    }
}
