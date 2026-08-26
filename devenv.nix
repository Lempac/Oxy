{ pkgs, ... }:
{
  name = "Oxy";
  languages = {
    javascript = {
      enable = true;
      package = pkgs.nodejs_24;
      pnpm.enable = true;
    };
    nix.enable = true;
  };
  packages = with pkgs; [ sqlite pocketbase electron nixd ];
  env = {
    ELECTRON_OVERRIDE_DIST_PATH = "${pkgs.electron}/libexec/electron";
  };
  enterShell = ''
    export ELECTRON_OVERRIDE_DIST_PATH="${pkgs.electron}/libexec/electron"
  '';
  processes = {
    pocketbase.exec = "pocketbase serve --dir=./pb_data --dev";
    hooks-watch.exec = "pnpm run watch:hooks";
    vite.exec = "pnpm run dev";
    y-web.exec = "pnpm run yjs";
  };
}
