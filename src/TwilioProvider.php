<?php

namespace NotificationChannels\Twilio;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use NotificationChannels\Twilio\Exceptions\InvalidConfigException;
use Twilio\Rest\Client as TwilioService;

class TwilioProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot() {}

    /**
     * Register the application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/twilio-notification-channel.php', 'twilio-notification-channel');

        $this->publishes([
            __DIR__.'/../config/twilio-notification-channel.php' => config_path('twilio-notification-channel.php'),
        ]);

        $this->app->bind(TwilioConfig::class, function () {
            return new TwilioConfig($this->app['config']['twilio-notification-channel']);
        });

        $this->app->singleton(LaravelHttpClient::class, function (Application $app) {
            /** @var TwilioConfig $config */
            $config = $app->make(TwilioConfig::class);

            return new LaravelHttpClient($config->getRetryTimes(), $config->getRetryDelay());
        });

        $this->app->singleton(TwilioService::class, function (Application $app) {
            /** @var TwilioConfig $config */
            $config = $app->make(TwilioConfig::class);

            if ($config->usingTokenAuth()) {
                return new TwilioService(
                    username: $config->getSid(),
                    password: $config->getAuthToken(),
                    accountSid: $config->getAccountSid(),
                    httpClient: $app->make(LaravelHttpClient::class),
                );
            }

            throw InvalidConfigException::missingConfig();
        });

        $this->app->singleton(Twilio::class, function (Application $app) {
            return new Twilio(
                $app->make(TwilioService::class),
                $app->make(TwilioConfig::class)
            );
        });

        $this->app->singleton(TwilioChannel::class, function (Application $app) {
            return new TwilioChannel(
                $app->make(Twilio::class),
                $app->make(Dispatcher::class)
            );
        });
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [
            LaravelHttpClient::class,
            TwilioChannel::class,
            TwilioConfig::class,
            TwilioService::class,
        ];
    }
}
