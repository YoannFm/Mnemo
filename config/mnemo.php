<?php

return [
    'version' => '1.0.1',

    /*
    |--------------------------------------------------------------------------
    | Mises à jour et plugins (GitHub)
    |--------------------------------------------------------------------------
    |
    | Les mises à jour sont lues depuis les Releases GitHub du dépôt : chaque
    | release doit contenir l'archive `release_asset`, générée par le workflow
    | `.github/workflows/release.yml`.
    | Le catalogue des plugins est un fichier JSON public (voir PLUGINS.md).
    |
    */

    'github' => [
        'repository'      => env('MNEMO_GITHUB_REPOSITORY', 'YoannFm/Mnemo'),
        'release_asset'   => 'mnemo.zip',
        'plugins_catalog' => env('MNEMO_PLUGINS_CATALOG', 'https://raw.githubusercontent.com/YoannFm/Mnemo/main/plugins.json'),
    ],
];
