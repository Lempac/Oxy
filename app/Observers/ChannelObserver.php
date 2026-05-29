<?php

namespace App\Observers;

use App\Models\Channel;
use Illuminate\Support\Str;

class ChannelObserver
{
    public function creating(Channel $channel)
    {
        $this->generateSlug($channel);
    }

    public function updating(Channel $channel)
    {
        if ($channel->isDirty('name')) {
            $this->generateSlug($channel);
        }
    }

    private function generateSlug(Channel $channel)
    {
        $slug = Str::slug($channel->name);
        $originalSlug = $slug;
        $count = 1;

        while (Channel::where('server_id', $channel->server_id)->where('slug', $slug)->where('id', '!=', $channel->id ?? 0)->exists()) {
            $slug = "{$originalSlug}-{$count}";
            $count++;
        }

        $channel->slug = $slug;
    }
}
