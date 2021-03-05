# Wordpress TigreBlanc Component

[![Latest Version](https://img.shields.io/badge/release-1.0.0-blue?style=for-the-badge)](https://www.presstify.com/pollen-solutions/wp-tb/)
[![MIT Licensed](https://img.shields.io/badge/license-MIT-green?style=for-the-badge)](LICENSE.md)

## Installation

```bash
composer require pollen-solutions/wp-tb
```

## Basic Usage
```php
use Pollen\WpTb\WpTb;

new Wptb();
```


## Pollen Framework Setup

### Declaration

```php
// config/app.php
use Pollen\WpTb\WpTbServiceProvider;

return [
      //...
      'providers' => [
          //...
          WpTbServiceProvider::class,
          //...
      ]
      // ...
];
```

### Configuration

```php
// config/wp-tb.php
// @see /vendor/pollen-solutions/wp-tb/resources/config/wp-tb.php
return [
      //...

      // ...
];
```
