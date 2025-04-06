# Laravel Tinker On VSCode

You can tinker with your application on vscode.

<img src="./images/demo4.png" width="800">
<img src="./images/demo5.png" width="800">

You can dump:

<img src="./images/demo3.png" width="800">

## Installation

```bash
composer require pkboom/laravel-tinker-on-vscode --dev
```

You can publish the config:

```bash
php artisan vendor:publish --provider="Pkboom\TinkerOnVscode\TinkerOnVscodeServiceProvider" --tag="config"
```

## Usage

```bash
php artisan tinker-on-vscode
```

You can show queries.

```bash
php artisan tinker-on-vscode --query
```

## License

The MIT License (MIT). Please see [MIT license](http://opensource.org/licenses/MIT) for more information.
