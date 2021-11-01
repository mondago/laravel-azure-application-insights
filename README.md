# Application Insights for Laravel

## Installation

Add the following to your _composer.json_ file:

```bash
"repositories": [
    {
        "type": "git",
        "url": "https://github.com/mondago/laravel-azure-application-insights"
    }
],
```

and require it by adding the following line to your _composer.json_ file under "require":

```
"mondago/laravel-application-insights": "^0.5.0"
```

Optionally, you can publish the config file of this package with this command:

```bash
php artisan vendor:publish --provider="Mondago\ApplicationInsights\ServiceProvider"
```

## Middleware

As a convenience you may choose to use the `Mondago\ApplicationInsights\Middleware\TrackRequest` middleware which will send the request and response to Application Insights. The middleware utilizes [terminable middleware](https://laravel.com/docs/8.x/middleware#terminable-middleware) from Laravel to ensure that tracking the request doesn't block the response being sent.
