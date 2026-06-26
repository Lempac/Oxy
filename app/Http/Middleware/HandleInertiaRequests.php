<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        if ($user) {
            $user->load('allRoles');
            $user->setRelation('roles', $user->allRoles);
        }

        return [
            ...parent::share($request),
            'user' => $user,
            'flash' => [
                'message' => fn () => $request->session()->get('message'),
                'error' => fn () => $request->session()->get('error'),
            ],
            'locale' => app()->getLocale(),
            'translations' => cache()->rememberForever('translations_'.app()->getLocale(), function () {
                $locale = app()->getLocale();
                $files = glob(lang_path($locale.'/*.php'));
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
