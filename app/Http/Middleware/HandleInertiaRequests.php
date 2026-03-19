<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        if ($user) {
            $user = $request->user()->load('roles');
        }

        return [
            ...parent::share($request),
            'user' => $user,
            'locale' => app()->getLocale(),
            'translations' => cache()->rememberForever('translations_' . app()->getLocale(), function () {
                $locale = app()->getLocale();
                $files = glob(lang_path($locale . '/*.php'));
                $translations = [];

                foreach ($files as $file) {
                    $name = basename($file, '.php');
                    $translations[$name] = require $file;
                }

                return $translations;
            }),
        ];
    }
}
