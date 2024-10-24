{ pkgs, lib, config, inputs, ... }:

{
#    dotenv.enable = true;
    languages.php.enable = true;
    languages.php.version = "8.3";
    languages.php.extensions = [ "xdebug" "pdo_mysql" ];
    languages.php.ini = ''
        xdebug.mode = debug
        xdebug.discover_client_host = 1
        xdebug.client_host = 127.0.0.1
    '';
    languages.javascript.enable = true  ;
    languages.javascript.package = pkgs.nodejs_20;

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
