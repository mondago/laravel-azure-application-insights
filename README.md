# Application Insights for Laravel



## Installation

Add the following to your _composer.json_ file:

``` bash
"repositories": [
    {
        "type": "git",
        "url": "https://github.com/mondago/laravel-azure-application-insights"
    }
],
```
and require it by adding the following line to your _composer.json_ file under "require":
```
"mondago/laravel-application-insights": "^0.3.2"
```


Optionally, you can publish the config file of this package with this command:

``` bash
php artisan vendor:publish --provider="Mondago\ApplicationInsights\ServiceProvider"
```
