<?php

namespace Plugins\Webhook;

use Illuminate\Support\ServiceProvider;

class WebhookServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');

        $this->loadViewsFrom(__DIR__ . '/resources/views', 'webhook');

        \App\Models\SharedExamAttempt::updated(function ($attempt) {
            if (
                $attempt->wasChanged('finished_at') &&
                $attempt->finished_at &&
                $attempt->sharedExam &&
                $attempt->sharedExam->webhook_url
            ) {
                $url = $attempt->sharedExam->webhook_url;
                $payload = [
                    'exam'        => $attempt->sharedExam->label ?? 'Examen partagé',
                    'module'      => $attempt->sharedExam->module->title ?? '',
                    'participant' => $attempt->guest_name,
                    'score'       => $attempt->score,
                    'total'       => $attempt->total,
                    'grade'       => $attempt->grade,
                    'percentage'  => $attempt->percentage,
                    'finished_at' => $attempt->finished_at?->toIso8601String(),
                ];
                try {
                    \Illuminate\Support\Facades\Http::timeout(5)->post($url, $payload);
                } catch (\Throwable) {}
            }
        });
    }
}
