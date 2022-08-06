![Automated bindings](https://user-images.githubusercontent.com/37669560/176007043-1e1b927e-3b44-468b-8b94-edbc91332603.png)

# Laravel Auto-Binder
[![Latest Version on Packagist](https://img.shields.io/packagist/v/michael-rubel/laravel-auto-binder.svg?style=flat-square&logo=packagist)](https://packagist.org/packages/michael-rubel/laravel-auto-binder)
[![Total Downloads](https://img.shields.io/packagist/dt/michael-rubel/laravel-auto-binder.svg?style=flat-square&logo=packagist)](https://packagist.org/packages/michael-rubel/laravel-auto-binder)
[![Code Quality](https://img.shields.io/scrutinizer/quality/g/michael-rubel/laravel-auto-binder.svg?style=flat-square&logo=scrutinizer)](https://scrutinizer-ci.com/g/michael-rubel/laravel-auto-binder/?branch=main)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/michael-rubel/laravel-auto-binder.svg?style=flat-square&logo=scrutinizer)](https://scrutinizer-ci.com/g/michael-rubel/laravel-auto-binder/?branch=main)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/michael-rubel/laravel-auto-binder/run-tests/main?style=flat-square&label=tests&logo=github)](https://github.com/michael-rubel/laravel-auto-binder/actions)
[![PHPStan](https://img.shields.io/github/workflow/status/michael-rubel/laravel-auto-binder/phpstan/main?style=flat-square&label=larastan&logo=laravel)](https://github.com/michael-rubel/laravel-auto-binder/actions)

This package adds the possibility to bind interfaces to concrete classes in the Service Container by scanning the specified project folders. This helps avoid manually registering container bindings when the project needs to bind a lot of interfaces to its implementations.

---

The package requires PHP `8.x` and Laravel `9.x`

## #StandWithUkraine
[![SWUbanner](https://raw.githubusercontent.com/vshymanskyy/StandWithUkraine/main/banner2-direct.svg)](https://github.com/vshymanskyy/StandWithUkraine/blob/main/docs/README.md)

## Installation
Install the package using composer:
```bash
composer require michael-rubel/laravel-auto-binder
```

## Usage

Define in your ServiceProvider:
```php
AutoBinder::from(folder: 'Services')
    ->as('singleton')
    ->bind();
```

Assuming you have your services in the `App\Services` and its interfaces in the `App\Services\Interfaces`, the package will register binding for each pair of class and interface:
```php
$this->app->singleton(AuthServiceInterface::class, AuthService::class);
$this->app->singleton(UserServiceInterface::class, UserService::class);
$this->app->singleton(CompanyServiceInterface::class, CompanyService::class);
...
```

### Customization

If you need to customize the base path or namespace, you can use following methods:
```php
AutoBinder::from(folder: 'Services')
    ->basePath('app/Domain')
    ->classNamespace('App\\Domain')
    ->interfaceNamespace('App\\Domain\\Interfaces')
    ->bind();
```
This configuration would look for classes in the `app/Domain/Services` folder, use `App\\Domain` namespace and apply interfaces from `App\\Domain\\Interfaces` namespace with `ClassNameInterface` naming convention.


If you need to change the naming convention of your interfaces, you can specify the namespace and name you prefer:
```php
AutoBinder::from(folder: 'Services')
    ->interfaceNaming('Contract')
    ->bind();
```
This configuration scans the `app/Services` folder with `App\\Services` namespace, `App\\Services\\Contracts` interface namespace and `ClassNameContract` interface naming convention.

### Excluding subfolders from scan

You might as well exclude subdirectories from the scan of the root directory:
```php
AutoBinder::from(folder: 'Services')
    ->exclude('Traits', 'Components')
    ->bind();
```

### Dependency injection

If you want to inject dependencies to your services while scanning, you can use `when` method:
```php
AutoBinder::from(folder: 'Services')
    ->when(ExampleServiceInterface::class, function ($app, $service) {
        return new ExampleService($app);
    })
    ->bind();
```
Passing a concrete class as well as an interface is possible, but keep in mind interfaces have a higher priority when applying dependencies.

### Scanning multiple folders at once

If you pass multiple folders, the `from` method will return an instance of `Illuminate/Support/Collection`. Assuming that, you can loop over your `AutoBinder` class instances with access to internal properties.

For example:
```php
AutoBinder::from('Services', 'Models')->each(
    fn ($binder) => $binder->basePath('app')
        ->classNamespace('App\\Domain')
        ->interfaceNamespace("App\\Domain\\$binder->classFolder\\Interfaces")
        ->as('singleton')
        ->bind()
);
```

### Caching
The package will cache your bindings to avoid redundant folder scans during the application bootstrapping process.

If you want cache to be disabled, you may use `withoutCaching` method on the AutoBinder instance, e.g.:
```php
AutoBinder::from(folder: 'Services')
    ->withoutCaching()
    ->as('singleton')
    ->bind();
```

You can as well clear the cache for specific folder by using an Artisan command:
```shell
php artisan binder:clear Services
```
Note that for the command to work you should use the folder name you passed to the AutoBinder instance in the `from` method. The cache will be ignored on local environment starting from `v5.0.2`

## Testing
```bash
composer test
```

## License
The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
