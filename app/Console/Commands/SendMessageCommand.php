<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Mail\Events\MessageSent;
use App\Events\test;

class SendMessageCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:message';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a message to the chat';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->ask('What is your name?');
        $text = $this->ask('What is your message?');

        test::broadcast($name, $text);
    }


}
