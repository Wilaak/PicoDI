# PicoDI 🗃️

A stupidly simple PHP dependency injection container.

### Notable Features: 

- **Autowiring**: Automatically resolves constructor dependencies using type hints.  
- **PSR-11 Compliant**: Implements the `ContainerInterface` for interoperability.  
- **Automatic Instantiation**: Automatically instantiates loadable classes without requiring explicit configuration.  

## Installation  

Install PicoDI using Composer:  

```bash
composer require wilaak/picodi
```

Requires PHP 8.3 or above

## Table of Contents

1. [Introduction](#introduction)  
2. [Usage](#usage)  
    - [Basic Example](#basic-example)  
    - [Autowiring](#autowiring)  
    - [Advanced Example](#advanced-example)  
    - [Configuration Options](#configuration-options)  
3. [Learn More](#learn-more)  
4. [Attribution](#attribution)  

## Introduction

PicoDI is a stupidly simple dependency injection container, providing only the bare essentials needed to get started. Dependency injection containers are powerful tools in software development. They help manage and centralize the creation and resolution of dependencies within an application. By using a dependency injection container, you can achieve cleaner, more maintainable code by adhering to the principles of Inversion of Control (IoC) and Dependency Inversion.



## Usage  

Below are examples and explanations to help you get started with the container.

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

### Configuration Options

The configuration array defines how the container resolves dependencies. Each key represents a class or interface, and the value specifies how to resolve it. Here are the supported value types:

1. **String or `::class` Syntax**:
   - Defines an alias for the class or interface in the key.
   - Example:
     ```php
     LoggerInterface::class => LoggerService::class,
     ```
     This maps the `LoggerInterface` to the `LoggerService` implementation.

2. **Closure (Factory Function)**:
   - Provides a custom factory function for creating the service.
   - Example:
     ```php
     LoggerService::class => function() {
         return new LoggerService(
             path: '/var/log/log.txt',
             level: 'debug'
         );
     },
     ```
     This allows for more control over how the object is instantiated.

3. **Array (Constructor Injection)**:
   - Maps constructor parameters by utilizing the aforementioned options.
   - Example:
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
     This enables constructor injection by specifying an alias or factory for each parameter.

## Learn More  
- [Understanding Dependency Injection](https://php-di.org/doc/understanding-di.html)  

## Attribution  
Thanks to [Evan Hildreth](https://github.com/oddevan) and his article: [A Stupidly Simple PHP Dependency Injection Container](https://oddevan.com/2023/08/31/a-stupidly-simple.html) for inspiring this project.