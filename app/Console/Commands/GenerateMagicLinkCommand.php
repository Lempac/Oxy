<?php

namespace App\Console\Commands;

use App\Enums\UserStatus;
use App\Models\Server;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;

class GenerateMagicLinkCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auth:magic-link {nickname=testuser} {--days=7}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a 1-click temporary signed login URL for preview testing';

    public function handle(): int
    {
        $nickname = $this->argument('nickname');

        // Seed demo content if the database is completely empty
        if (Server::count() === 0) {
            $this->callSilent('db:seed', ['--force' => true]);
        }

        $user = User::firstOrCreate(
            ['nickname' => $nickname],
            [
                'password' => Hash::make('password'),
                'status' => UserStatus::Online->value,
                'about_me' => 'Preview testing account',
            ]
        );

        $days = (int) $this->option('days');
        $url = URL::temporarySignedRoute(
            'magic-login',
            now()->addDays($days),
            ['user' => $user->id]
        );

        $this->line($url);

        return self::SUCCESS;
    }
}
