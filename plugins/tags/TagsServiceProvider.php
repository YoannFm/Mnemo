<?php

namespace Plugins\Tags;

use App\Extensions\Plugin\BasePluginServiceProvider;
use Plugins\Tags\Controllers\TagController;

class TagsServiceProvider extends BasePluginServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrations();
        $this->loadViews();

        // Register routes
        $this->router->middleware(['web', 'auth'])->group(function () {
            $this->router->get('/tags', fn() => redirect('/modules'))->name('tags.index');
            $this->router->post('/tags', [TagController::class, 'store'])->name('tags.store');
            $this->router->post('/modules/{module}/tags', [TagController::class, 'attach'])->name('modules.tags.attach');
            $this->router->delete('/modules/{module}/tags/{tag}', [TagController::class, 'detach'])->name('modules.tags.detach');
        });

        // Add tags relationship to Module dynamically
        \App\Models\Module::resolveRelationUsing('tags', function ($module) {
            return $module->belongsToMany(\Plugins\Tags\Models\Tag::class, 'module_tag');
        });

        // Inject tags into library view
        $this->app['view']->composer(['library', 'library.index', 'modules.library'], function ($view) {
            try {
                $tags = \Plugins\Tags\Models\Tag::orderBy('name')->get();
            } catch (\Throwable) {
                $tags = collect();
            }
            $view->with('libraryTags', $tags);
        });

        // Inject tags into module create/edit views
        $this->app['view']->composer(['modules.create', 'modules.edit'], function ($view) {
            try {
                $allTags = \Plugins\Tags\Models\Tag::orderBy('name')->get();
            } catch (\Throwable) {
                $allTags = collect();
            }
            $view->with('allTags', $allTags);
        });
    }
}
