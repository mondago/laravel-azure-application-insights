# Application Insights for Laravel

## Installation

Add the following to your _composer.json_ file:

```bash
"repositories": [
    {
      "type": "git",
      "url": "https://github.com/mondago/ApplicationInsights-PHP"
    },
    {
        "type": "git",
        "url": "https://github.com/mondago/laravel-azure-application-insights"
    }
],
```

and require it by adding the following line to your _composer.json_ file under "require":

```
"mondago/laravel-application-insights": "^0.6.0"
```

Optionally, you can publish the config file of this package with this command:

```bash
php artisan vendor:publish --provider="Mondago\ApplicationInsights\ServiceProvider"
```

## Middleware

As a convenience you may choose to use the `Mondago\ApplicationInsights\Middleware\TrackRequest` middleware which will send the request and response to Application Insights.
The middleware utilizes [terminable middleware](https://laravel.com/docs/8.x/middleware#terminable-middleware) from Laravel to ensure that tracking the request doesn't block the response being sent.

Since 0.6.2, if the request has a session started, this middleware will set the session id as the anonymous user id to allow tracking of user flows.
**Make sure to include this in each route group you'd like to track requests for, as attaching it to the global middleware will result in the session never being set**.
You can disable this by setting the environment variable `APPINSIGHTS_TRACK_ANONYMOUS_USERS` to `false`.

## Logging

Since 0.6.2 a custom monolog handler is provided for sending logs to application insights.
This handler captures exceptions as well as other log levels.
Exceptions are tracked as exceptions, and log messages are sent with their closes matching severity level.

To use this, update your `config/logging.php` configuration with the following:

```php
'channels' => [
    // other channels...
    'app-insights' => [
        'driver' => 'custom',
        'via' => Mondago\ApplicationInsights\Logging\CreateApplicationInsightsLogger::class,
    ],
]
```

You might then choose to include this in your `stack` log handler:

```php
'channels' => [
    // other channels...
    'stack' => [
        'driver' => 'stack',
        'channels' => ['stderr', 'daily', 'app-insights'],
    ],
]
```

## Logging Migrations

This package will automatically send migration events to application insights when enabled.
Since 0.6.2 this includes when no migrations were ran. This is great for container images that run `php artisan migrate` on startup.

## Support Matrix

| Our Version | Laravel Version |
|-------------|-----------------|
| ^0.6.0      | ^9.0            |
| ^0.4.0      | ^8.0            |
| ^0.3.0      | ^7.0            |
