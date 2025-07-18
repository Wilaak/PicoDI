# PicoDI 

Ridiculously simple dependency injection (DI) container for PHP.

### Overview

- All services are singletons; each dependency is instantiated only once and shared (lazy-loading).
- Extremely lightweight: just 86 lines of code.
- Supports automatic constructor injection using type hints (autowiring).
- Fully compatible with the PSR-11 container interface.

## What is a DI container?

A dependency injection container is a tool that automatically creates and manages the objects your application needs, wiring their dependencies together for you. It centralizes how services are configured and connected, making your code easier to maintain, test, and extend.

**Without a container:**

```PHP
// You must manually create and pass every dependency, everywhere you need them.
$logger = new FileLogger('/tmp/app.log');
$mailer = new Mailer($logger);

$userService = new UserService($logger, $mailer);
$orderService = new OrderService($logger, $mailer);

// If you need these services in other places, you have to repeat this process.
// If you want to swap the logger implementation, you must update every place it's used.

// Adding a new dependency or changing the wiring means editing code in many places.
```

**With a container:**

```PHP
// Configure how your objects should be instantiated.
$config = [
    LoggerInterface::class => FileLogger::class,
    FileLogger::class => function() { 
        return new FileLogger(
            file: '/tmp/app.log'
        );
    }
    Mailer::class => function(ServiceContainer $container) {
        return new Mailer(
            logger: $container->get(LoggerInterface::class)
        );
    }
];

$container = new ServiceContainer($config);

// The container will automatically inspect the constructor and inject its dependencies.
$userService = $container->get(UserService::class);
$orderService = $container->get(OrderService::class);

// If you want to swap the logger implementation, just change the config in one place!
```

## Installation

Install using composer:

```bash
composer require wilaak/picodi
```

Requires PHP 8.0 or above

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

    FileLogger::class => function(ServiceContainer $container) {
        // You can use $container->get to resolve other dependencies if needed
        // For this example, we just instantiate FileLogger directly
        return new FileLogger('/tmp/app.log');
    }
];

$container = new ServiceContainer($config);

$userService = $container->get(UserService::class);
$userService->createUser('alice');
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
        $logger = new FileLogger(file: '/var/log/app.log');
        // Optionally, inject other services from the container if needed:
        // $dependency = $container->get(OtherService::class);
        return $logger;
    },
];
```

## Credits  

This container is based on the one from the article [A Stupidly Simple PHP Dependency Injection Container](https://oddevan.com/2023/08/31/a-stupidly-simple.html) from [Evan Hildreth](https://github.com/oddevan).