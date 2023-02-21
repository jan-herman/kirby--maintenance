<?php

use Kirby\Cms\App as Kirby;

Kirby::plugin('jan-herman/kirby-maintenance', [
    'blueprints' => [
        'settings/maintenance' => __DIR__ . '/blueprints/settings/maintenance.yml',
    ],
    'routes' => [
        [
            'pattern' => '(:all)',
            'env' => 'site',
            'language' => '*',
            'action'  => function ($language) {
                $kirby = kirby();

                // Workaround for https://github.com/getkirby/kirby/issues/2428
                $kirby->setCurrentTranslation($language);
                $kirby->setCurrentLanguage($language);

                $site = $kirby->site();

                // check if is plugin enabled
                if (!$site->maintenance_enabled()->toBool()) {
                    $this->next();
                }

                // check if is user logged-in
                if (!$kirby->user()) {
                    $redirect_url = $site->maintenance_redirect_url()->isNotEmpty() ? $site->maintenance_redirect_url()->toUrl() : $site->panel()->url();
                    go($redirect_url, 302);
                }

                // continue the default route
                $this->next();
            }
        ]
    ]
]);
