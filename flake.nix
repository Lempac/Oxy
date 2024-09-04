{
  description = "laravel flake";

  inputs = {
    nixpkgs.url = "github:nixos/nixpkgs?ref=nixos-unstable";
  };

  outputs = { self, nixpkgs }:
  let
        system = "x86_64-linux";
        pkgs = nixpkgs.legacyPackages.${system};
        php = pkgs.php83.buildEnv {
#            extensions = ({ enabled, all }: enabled ++ (with all; [
#              xdebug
#            ]));
#            extraConfig = ''
#              ; xdebug 3
#              xdebug.mode=debug
#              xdebug.client_port=9000
#
#              ; xdebug 2
#              xdebug.remote_enable=1
#            '';
        };
  in
  {
    devShells.${system}.default =
    pkgs.mkShell
    {
        buildInputs = with pkgs; [
            php
            php.packages.composer
            pkgs.nodejs_20
        ];
    };
  };
}
