# PicoDI 

The ridiculously simple dependency injection (DI) container for PHP.

### Overview

- All services are singletons; each dependency is instantiated only once and shared (lazy-loading).
- Extremely lightweight: just 86 lines of code.
- Supports automatic constructor injection using type hints (autowiring).
- Fully compatible with the PSR-11 container interface.

## How does it work?

Ojects often depend on other objects to do their job. Manually injecting these dependencies everywhere can be tiresome and error-prone, especially when you need to use them in multiple places.

A dependency injection container aims to help by acting as a "service directory": you configure each service once, and the container handles creating and wiring everything together automatically.

**Without a container:**

```PHP
$logger = new FileLogger('/tmp/app.log');
$userService = new UserService($logger);
```

**With PicoDI:**

```PHP
$config = [
    LoggerInterface::class => FileLogger::class,
    FileLogger::class => fn() => new FileLogger('/tmp/app.log'),
];

$container = new ServiceContainer($config);
$userService = $container->get(UserService::class); // Logger is auto-injected!
```

## Installation

Install using composer:

```bash
composer require wilaak/picodi
```

Requires PHP 8.1 or above

## Usage example

Here's a basic usage example.

```php
use Wilaak\PicoDI\ServiceContainer;

interface LoggerInterface {
    public function log(string $message): void;
}

class FileLogger implements LoggerInterface {
    public function __construct(private string $file) {}
    public function log(string $message): void {
        file_put_contents($this->file, $message . PHP_EOL, FILE_APPEND);
    }
}

class UserService {
    public function __construct(private LoggerInterface $logger) {}
    public function createUser(string $username): void {
        $this->logger->log("Created user: $username");
    }
}

$config = [
    LoggerInterface::class => FileLogger::class,
    FileLogger::class => fn() => new FileLogger('/tmp/app.log'),
];

$container = new ServiceContainer($config);

$userService = $container->get(UserService::class);
```

## Configuration

The configuration array specifies how the container resolves dependencies. The following key value types are supported:

### String (Alias)

Defines an alias for the class or interface specified in the key. You can use either the class name resolution operator or plain strings, but the `::class` syntax is preferred for readability and IDE support.

```php
$config = [
    LoggerInterface::class => FileLogger::class,
];
```

### Callable (Factory Function)

Use a callable (e.g anonymous functions) to explicitly return the desired value.

```php
$config = [
    LoggerInterface::class => function(ServiceContainer $container) {
        // You can use the container to resolve dependencies or configure the instance.
        $logger = new FileLogger(
            file: '/var/log/app.log'
        );
        // Optionally, inject other services from the container if needed:
        // $dependency = $container->get(OtherService::class);
        return $logger;
    },
];
```

## Credits  

This container is based on the one from the article [A Stupidly Simple PHP Dependency Injection Container](https://oddevan.com/2023/08/31/a-stupidly-simple.html) from [Evan Hildreth](https://github.com/oddevan).