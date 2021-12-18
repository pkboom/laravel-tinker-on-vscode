# Laravel Tinker On VSCode

[![Latest Stable Version](https://poser.pugx.org/pkboom/laravel-tinker-on-vscode/v/stable)](https://packagist.org/packages/pkboom/laravel-tinker-on-vscode)
[![Total Downloads](https://poser.pugx.org/pkboom/laravel-tinker-on-vscode/downloads)](https://packagist.org/packages/pkboom/laravel-tinker-on-vscode)
[![Build Status](https://travis-ci.com/pkboom/laravel-tinker-on-vscode.svg?branch=master)](https://travis-ci.com/pkboom/laravel-tinker-on-vscode)

You can tinker with your application on vscode.

<img src="/images/demo2.png" width="800"  title="demo">

With query:

<img src="/images/demo1.png" width="800"  title="demo">

You can also output data to the console with `dd()` or `dump()`.

<img src="/images/demo3.png" width="800">

## Installation

```bash
composer require pkboom/laravel-tinker-on-vscode --dev
```

## Usage

```bash
php artisan tinker-on-vscode
```

You can show queries.

```bash
php artisan tinker-on-vscode --query
```

You can dump data to dump server.

```bash
php artisan tinker-on-vscode --dump
```

If you only want to dump data,

```bash
php artisan dump-server
```

## License

The MIT License (MIT). Please see [MIT license](http://opensource.org/licenses/MIT) for more information.
