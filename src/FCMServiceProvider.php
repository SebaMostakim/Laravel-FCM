<?php

namespace LaravelFCM;

use Illuminate\Support\Facades\Str;
use LaravelFCM\Sender\FCMGroup;
use LaravelFCM\Sender\FCMSender;
use Illuminate\Support\ServiceProvider;

class FCMServiceProvider extends ServiceProvider
{
    /**
     * Register services in the container.
     *
     * @return void
     */
    public function register()
    {
        // Merge configuration for non-Lumen applications
        if (!$this->isLumen()) {
            $this->mergeConfigFrom(__DIR__ . '/../config/fcm.php', 'fcm');
        }

        // Register singleton for FCM client
        $this->app->singleton('fcm.client', function ($app) {
            return (new FCMManager($app))->driver();
        });

        // Register FCM group binding
        $this->app->bind('fcm.group', function ($app) {
            $client = $app['fcm.client'];
            $url = $app['config']->get('fcm.http.server_group_url');

            return new FCMGroup($client, $url);
        });

        // Register FCM sender binding
        $this->app->bind('fcm.sender', function ($app) {
            $client = $app['fcm.client'];
            $url = $app['config']->get('fcm.http.server_send_url');

            return new FCMSender($client, $url);
        });
    }

    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->isLumen()) {
            $this->app->configure('fcm');
        } else {
            // Publish configuration file for Laravel applications
            $this->publishes([
                __DIR__ . '/../config/fcm.php' => config_path('fcm.php'),
            ], 'fcm-config');
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['fcm.client', 'fcm.group', 'fcm.sender'];
    }

    /**
     * Determine if the application is running under Lumen.
     *
     * @return bool
     */
    protected function isLumen()
    {
        return str_contains($this->app->version(), 'Lumen');
    }
}
