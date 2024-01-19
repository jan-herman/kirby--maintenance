<?php

use Kirby\Cms\App as Kirby;
use Kirby\Cms\Page;
use Kirby\Filesystem\F;

Kirby::plugin('jan-herman/kirby-maintenance', [
    'blueprints' => [
        'sections/settings/maintenance' => __DIR__ . '/blueprints/maintenance.yml',
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
                if ($site->content($language->code())->maintenance_enabled()->isFalse()) {
                    $this->next();
                }

                // check if is user logged-in
                if (!$kirby->user()) {
                    if ($site->maintenance_redirect_url()->isNotEmpty()) { // redirect to specified url
                        go($site->maintenance_redirect_url()->toUrl(), 302);
                    } elseif (F::exists($kirby->root('templates') . '/maintenance.php') || F::exists($kirby->root('templates') . '/maintenance.latte')) { // render maintenance template
                        return new Page([
                            'slug'     => 'home',
                            'template' => 'maintenance'
                        ]);
                    } else { // redirect to login
                        go($site->panel()->url(), 302);
                    }
                }

                // continue the default route
                $this->next();
            }
        ]
    ]
]);
