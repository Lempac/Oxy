<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Events\Message;

class SendMessageCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:message {userId?} {channelId?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a message to the chat';

    public function handle(): void
    {
        $userId = $this->argument('userId') ?? $this->ask('What is the user ID?');

        $channelId = $this->argument('channelId') ?? $this->ask('What is the channel ID?');

        $text = $this->ask('What is your message?');

        event(new Message($text, $userId, $channelId));

        $this->info('Message sent successfully!');
        $timezone = now()->getTimezone();
        $this->info("The current server timezone is: " . $timezone);
    }
}
