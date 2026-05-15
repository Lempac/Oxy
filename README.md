# Oxy
## About
This is a project for learning the development of social team collaboration tool,
that lets you create, manage groups with roles in company or group of people with goal. 
![UI](/assets/ui.png)

### The groups have the ability to chat or voice call.
![Group](/assets/group.png)
![Group Chat](/assets/group_chat.png)
![Group Voice Chat](/assets/group_voice_chat.png)

### And collaborate on whiteboard.

### Settings for managing groups and whiteboards.
![Settings](/assets/group_settings.png)

## How to build our project

## Dependencies

### PHP Dependencies
- laravel/framework: ^11.9
- barryvdh/laravel-dompdf: ^3.0
- inertiajs/inertia-laravel: ^1.0
- intervention/image: ^3.9
- intervention/image-laravel: ^1.3
- laravel/prompts: ^0.3.0
- laravel/reverb: ^1.3
- laravel/sanctum: ^4.0
- laravel/tinker: ^2.9
- pusher/pusher-php-server: *
- tightenco/ziggy: ^2.0

### JavaScript Dependencies
- @inertiajs/vue3: ^1.2.0
- @vitejs/plugin-vue: ^5.1.4
- @vue/server-renderer: ^3.5.12
- autoprefixer: ^10.4.20
- axios: ^1.7.7
- daisyui: ^4.12.14
- laravel-echo: ^1.16.1
- laravel-vite-plugin: ^1.0.5
- mockery/mockery: ^1.6
- postcss: ^8.4.47
- pusher-js: ^8.4.0-rc2
- tailwindcss: ^3.4.14
- typescript: 5.6.2
- vite: ^5.4.11
- vue: ^3.5.12
- vue-tsc: ^2.1.10

### Optional Dependencies
- bad-words: ^4.0.0
- oh-vue-icons: ^1.0.0-rc3
- fakerphp/faker: ^1.23
- laravel/breeze: ^2.1
- laravel/pint: ^1.13
- laravel/sail: ^1.26
- nunomaduro/collision: ^8.0
- pestphp/pest: ^2.0
- pestphp/pest-plugin-laravel: ^2.0
To build the project just run theese commands:

php artisan storage:link
php artisan reverb:start
php artisan queue:work 

This will set up the project and ensure that it works.
### If you don't have an email server, go to User.php and remove this line:
```php
use Illuminate\Contracts\Auth\MustVerifyEmail;
```
 
## To run the site
To run the site you will need XAMPP (Apache and MySQL)
and run these 2 command in the terminal:

npm run dev
php artisan serve

