<?php

namespace Plugins\QuizletImport;

use App\Extensions\Plugin\BasePluginServiceProvider;
use Illuminate\Support\Facades\Route;

class QuizletImportServiceProvider extends BasePluginServiceProvider
{
    public function boot(): void
    {
        $this->loadViews();

        Route::middleware(['web', 'auth'])->group(function () {
            Route::get('/quizlet-import', [Controllers\QuizletImportController::class, 'show'])
                ->name('quizlet-import.show');
            Route::post('/quizlet-import', [Controllers\QuizletImportController::class, 'import'])
                ->name('quizlet-import.import');
        });

        $this->registerUserNavigation();
    }

    protected function userNavigation(): array
    {
        return [
            [
                'label' => 'Import Quizlet',
                'url'   => url('/quizlet-import'),
                'icon'  => 'bi-upload',
            ],
        ];
    }
}
