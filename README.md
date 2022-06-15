![Laravel Auto Binder](https://user-images.githubusercontent.com/37669560/145568267-0498caf2-fb8a-4715-85ee-6374b8adadc5.png)

# Laravel Auto Binder
[![Latest Version on Packagist](https://img.shields.io/packagist/v/michael-rubel/laravel-auto-binder.svg?style=flat-square&logo=packagist)](https://packagist.org/packages/michael-rubel/laravel-auto-binder)
[![Total Downloads](https://img.shields.io/packagist/dt/michael-rubel/laravel-auto-binder.svg?style=flat-square&logo=packagist)](https://packagist.org/packages/michael-rubel/laravel-auto-binder)
[![Code Quality](https://img.shields.io/scrutinizer/quality/g/michael-rubel/laravel-auto-binder.svg?style=flat-square&logo=scrutinizer)](https://scrutinizer-ci.com/g/michael-rubel/laravel-auto-binder/?branch=main)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/michael-rubel/laravel-auto-binder.svg?style=flat-square&logo=scrutinizer)](https://scrutinizer-ci.com/g/michael-rubel/laravel-auto-binder/?branch=main)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/michael-rubel/laravel-auto-binder/run-tests/main?style=flat-square&label=tests&logo=github)](https://github.com/michael-rubel/laravel-auto-binder/actions)
[![PHPStan](https://img.shields.io/github/workflow/status/michael-rubel/laravel-auto-binder/phpstan/main?style=flat-square&label=larastan&logo=laravel)](https://github.com/michael-rubel/laravel-auto-binder/actions)

This package automatically binds interfaces to implementations in the Service Container by scanning the specified project folder. This helps avoid manually registering container bindings when the project needs to bind a lot of interfaces to its implementations without any additional dependencies.

---

The package requires PHP `^8.x` and Laravel `^8.71` or `^9.0`.

## #StandWithUkraine
[![SWUbanner](https://raw.githubusercontent.com/vshymanskyy/StandWithUkraine/main/banner2-direct.svg)](https://github.com/vshymanskyy/StandWithUkraine/blob/main/docs/README.md)

## Installation
Install the package using composer:
```bash
composer require michael-rubel/laravel-auto-binder
```

## Usage

```php
AutoBinder::from(folder: 'Services')
    ->basePath('app')
    ->classNamespace('App')
    ->interfaceNamespace('Interfaces')
    ->as('singleton')
    ->bind()
```

This will do the next job for you:
```php
$this->app->singleton(AuthServiceInterface::class, AuthService::class);
$this->app->singleton(UserServiceInterface::class, UserService::class);
$this->app->singleton(CompanyServiceInterface::class, CompanyService::class);
...
```

## Testing
```bash
composer test
```
