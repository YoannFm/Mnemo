<?php

namespace Plugins\SocialShare;

use App\Extensions\Plugin\BasePluginServiceProvider;

class SocialShareServiceProvider extends BasePluginServiceProvider
{
    public function boot(): void
    {
        $this->loadViews();

        $this->app['view']->composer('modules.show', function ($view) {
            $data = $view->getData();
            $module = $data['module'] ?? null;
            if ($module) {
                $shareUrl = url('/modules/' . $module->id);
                $shareText = urlencode('Je révise "' . $module->title . '" sur Mnemo !');
                $view->with([
                    'socialShareUrl'   => $shareUrl,
                    'socialShareText'  => $shareText,
                    'socialShareTitle' => $module->title,
                    'socialShareDesc'  => $module->description ?? ('Module de ' . ($module->items_count ?? '?') . ' items'),
                ]);
            }
        });
    }
}
