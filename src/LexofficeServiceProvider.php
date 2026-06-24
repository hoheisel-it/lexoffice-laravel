<?php

namespace HoheiselIT\Lexoffice;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use HoheiselIT\Lexoffice\Commands\LexofficeSyncCommand;
use HoheiselIT\Lexoffice\Http\Middleware\VerifyLexofficeSignature;

class LexofficeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/lexoffice.php', 'lexoffice');

        $this->app->singleton(LexofficeClient::class, function () {
            return new LexofficeClient(
                apiKey: config('lexoffice.api_key'),
                baseUrl: config('lexoffice.base_url'),
            );
        });

        $this->app->singleton(WebhookProcessor::class);
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/lexoffice.php' => config_path('lexoffice.php'),
            ], 'lexoffice-config');

            $this->publishes([
                __DIR__ . '/../database/migrations/' => database_path('migrations'),
            ], 'lexoffice-migrations');

            $this->commands([LexofficeSyncCommand::class]);
        }

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        $this->registerWebhookRoute();
    }

    private function registerWebhookRoute(): void
    {
        /** @var Router $router */
        $router = $this->app['router'];

        $router->aliasMiddleware('lexoffice.signature', VerifyLexofficeSignature::class);

        $middleware = array_merge(
            config('lexoffice.webhook.middleware', ['api']),
            ['lexoffice.signature'],
        );

        $router->group(['middleware' => $middleware], function () {
            $this->loadRoutesFrom(__DIR__ . '/../routes/webhooks.php');
        });
    }
}
