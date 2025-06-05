# PicoDI 🗃️

A stupidly simple PHP dependency injection container.

## Installation  

Install PicoDI using Composer:  

```bash
composer require wilaak/picodi
```

Requires PHP 8.3 or above


## Table of Contents

- [Usage examples](#usage-examples)
    - [Basic Example](#basic-example)
    - [Autowiring](#autowiring)
    - [Advanced Example](#advanced-example)
- [Configuration Options](#configuration-options)
    - [1. String or `::class` Syntax](#1-string-or-class-syntax)
    - [2. Closure (Factory Function)](#2-closure-factory-function)
    - [3. Array (Constructor Injection)](#3-array-constructor-injection)
- [Learn](#learn)
- [Attribution](#attribution)

## Usage examples

Below are some examples to help you get started with the container.

### Basic Example  
```php
interface LoggerInterface {}

class LoggerService implements LoggerInterface {}

$config = [
    LoggerInterface::class => LoggerService::class,
];

$container = new Wilaak\PicoDI\ServiceContainer($config);
$logger = $container->get(LoggerInterface::class);
```

### Autowiring  

PicoDI supports autowiring, allowing you to type-hint constructor parameters without explicit configuration:  

```php
class Bar {
    public function hello() { echo 'Hello, World!'; }
}

class Foo {
    public function __construct(
        private Bar $bar
    ) {}
}

$container = new Wilaak\PicoDI\ServiceContainer([]);
$foo = $container->get(Foo::class);
$foo->bar->hello();
```

### Advanced Example

```php
class DatabaseService {
    public function __construct(
        string $dsn,
        ?string $username = null,
        ?string $password = null,
        array $options = [],
        private ?LoggerInterface $logger = null
    ) {}
}

$config = [
    LoggerInterface::class => LoggerService::class,
    LoggerService::class => function() {
        return new LoggerService(
            logPath: config('log_path'),
            logLevel: config('log_level'),
        );
    },
    DatabaseService::class => [
        'dsn'      => fn() => 'mysql:host=localhost;dbname=mydb;charset=utf8mb4',
        'username' => fn() => 'dbuser',
        'password' => fn() => 'secret',
        'options'  => fn() => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ],
        'logger' => LoggerService::class,
    ],
];

$container = new Wilaak\PicoDI\ServiceContainer($config);
$databaseService = $container->get(DatabaseService::class);
```

## Configuration Options

The configuration array specifies how the container resolves dependencies. Each key serves as an identifier, usually the fully qualified class or interface name, while the value defines the resolution strategy. The following value types are supported:

### 1. String or `::class` Syntax (Alias)

Defines an alias for the class or interface specified in the key.

```php
LoggerInterface::class => LoggerService::class,
```
This maps the `LoggerInterface` to the `LoggerService` implementation. Using `::class` is essentially the same as providing a string, but we prefer the `::class` syntax because it is more readable and plays better with autocompletion.

Here’s the same example without using the `::class` syntax:

```php
'LoggerInterface' => 'LoggerService',
```
This achieves the same result, but using strings directly can be less readable.

### 2. Closure (Factory Function)

Provides a custom factory function for creating the service. Use a closure to explicitly return the desired value.

```php
LoggerService::class => function() {
    return new LoggerService(
        path: '/var/log/log.txt',
        level: 'debug'
    );
},
```

### 3. Array (Constructor Injection)

Allows you to define constructor arguments for a class using an associative array. Each key corresponds to the name of a constructor parameter, while the value specifies how it should be resolved. You can also use positional arguments by omitting the keys.

- Use a string or `::class` syntax to alias another implementation that will be resolved from the container.
- Use a closure to assign a value directly.

```php
DatabaseService::class => [
    'dsn'      => fn() => 'mysql:host=localhost;dbname=mydb;charset=utf8mb4',
    'username' => fn() => 'dbuser',
    'password' => fn() => 'secret',
    'options'  => fn() => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ],
    'logger' => LoggerService::class,
],
```

## Learn

- [Understanding Dependency Injection](https://php-di.org/doc/understanding-di.html)  

## Attribution  

Thanks to [Evan Hildreth](https://github.com/oddevan) and his article: [A Stupidly Simple PHP Dependency Injection Container](https://oddevan.com/2023/08/31/a-stupidly-simple.html) for inspiring this project.