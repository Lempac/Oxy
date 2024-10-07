{ pkgs, lib, config, inputs, ... }:

{
  # https://devenv.sh/basic                     s/
  #dotenv.enable = true;

  # https://devenv.sh/packages/
  packages = [ pkgs.git ];

  # https://devenv.sh/languages/
  # languages.rust.enable = true;
    languages.php.enable = true;
    languages.php.version = "8.3";
#    languages.php.package = pkgs.php.buildEnv {
#      extensions = { all, enabled }: with all; enabled ++ [ xdebug mysql ];
#      extraConfig = ''
#        xdebug.mode = debug
#        xdebug.discover_client_host = 1
#        xdebug.client_host = 127.0.0.1
#      '';
#    };
    languages.php.extensions = [ "xdebug" "pdo_mysql" ];
    languages.php.ini = ''xdebug.mode = debug
        xdebug.discover_client_host = 1
        xdebug.client_host = 127.0.0.1
    '';
    languages.javascript.enable = true  ;
    languages.javascript.package = pkgs.nodejs_20;
  # https://devenv.sh/processes/
  # processes.cargo-watch.exec = "cargo-watch";

  # https://devenv.sh/services/
  # services.postgres.enable = true;
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
  # https://devenv.sh/scripts/
#  scripts.hello.exec = ''
#    echo hello from $GREET
#  '';

#  enterShell = ''
#    echo $APP_ENV
#    git --version
#  '';

  # https://devenv.sh/tests/
#  enterTest = ''
#    set APP_ENV=testing
#  '';

  # https://devenv.sh/pre-commit-hooks/
  # pre-commit.hooks.shellcheck.enable = true;

  # See full reference at https://devenv.sh/reference/options/
}
