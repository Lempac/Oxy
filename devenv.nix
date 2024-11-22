{ pkgs, lib, config, inputs, ... }:

{
    name = "Oxy";
#    dotenv.enable = true;
    languages.php.enable = true;
    languages.php.version = "8.3";
    languages.php.extensions = [ "xdebug" "pdo_mysql" ];
    languages.php.ini = ''
        xdebug.mode = debug
        xdebug.discover_client_host = 1
        xdebug.client_host = 127.0.0.1
        upload_max_filesize = 200M
        post_max_size = 200M
    '';
    languages.javascript.enable = true;
    languages.javascript.package = pkgs.nodejs_20;

    processes = {
        vite.exec = "npm run dev";
        php-serve.exec = "php artisan serve";
        php-queue.exec = "php artisan queue:work";
        php-reverb.exec = "php artisan reverb:start";
    };

    services.mailpit.enable = true;
    services.mysql.enable = true;
    services.mysql.ensureUsers = [
        {
            name = "laravel";
            password = "laravel123";
            ensurePermissions = {
              "laravel.*" = "ALL PRIVILEGES";
            };
        }
    ];
}
