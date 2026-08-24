{ pkgs, ... }:
{
  name = "Oxy";
  languages = {
    php = {
      enable = true;
      version = "8.5";
      extensions = [ "xdebug" "pdo_mysql" "gd" ];
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
  packages = with pkgs; [ sqlite pocketbase nixd ];
  processes = {
    pocketbase.exec = "pocketbase serve --dir=./pb_data --dev";
    hooks-watch.exec = "pnpm run watch:hooks";
    vite.exec = "pnpm run dev";
    y-web.exec = "pnpm run yjs";
  };
}
