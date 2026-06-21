{ pkgs, ... }:
{
  name = "Oxy";
  languages = {
    php = {
      enable = true;
      version = "8.5";
      extensions = [ "xdebug" "pdo_mysql" ];
      ini = ''
        xdebug.mode = debug
        xdebug.discover_client_host = 1
        xdebug.client_host = 127.0.0.1
        upload_max_filesize = 200M
        post_max_size = 200M
      '';
    };
    javascript = {
      enable = true;
      package = pkgs.nodejs_24;
      pnpm.enable = true;
    };
    nix.enable = true;
  };
  packages = with pkgs; [ sqlite nixd ];
  processes = {
    vite.exec = "npm run dev";
    y-web.exec = "npm run yjs";
    php-serve.exec = "php artisan serve";
    php-queue.exec = "php artisan queue:work";
    php-reverb.exec = "php artisan reverb:start";
  };
}
