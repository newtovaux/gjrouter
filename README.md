# GJRouter

[![Total Downloads](https://img.shields.io/packagist/dt/newtovaux/gjrouter.svg)](https://packagist.org/packages/newtovaux/gjrouter)
[![Latest Stable Version](https://img.shields.io/packagist/v/newtovaux/gjrouter.svg)](https://packagist.org/packages/newtovaux/gjrouter)
![Continuous Integration](https://github.com/newtovaux/gjrouter/workflows/Continuous%20Integration/badge.svg)

A simple PHP Router incorporating JWT authenticator.

## Packagist

GJRouter is available at [Packagist](https://packagist.org/packages/newtovaux/gjrouter).
## Installation

Install the latest version with [Composer](https://getcomposer.org/):

```bash
composer require newtovaux/gjrouter
```

If you only need this library during development, for instance to run your project's test suite, then you should add it as a development-time dependency:

```bash
composer require --dev newtovaux/gjrouter
```

## Basic Usage

See the [examples/index.php](examples/index.php) file for a full example.

## About

### Source

Source is available on GitHub: https://github.com/newtovaux/gjrouter

### Requirements

- GJRouter `^1.0` is tested with PHP 7.4 or above, but *may* work with earlier versions.

### License

GJRouter is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Development

Create autoload:

```bash
composer dump-autoload -o
```

Static Analysis:

```bash
./vendor/bin/psalm --show-info=true
```

Run tests:

```bash
./vendor/bin/phpunit tests
```
