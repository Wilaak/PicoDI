<?php

require 'vendor/autoload.php';

// load all files from the src directory
$files = glob(__DIR__ . '/src/*.php');
foreach ($files as $file) {
    require_once $file;
}

use Wilaak\PicoDI\ServiceContainer;

interface LoggerInterface {
    public function log(string $message): void;
}

class FileLogger implements LoggerInterface {

    public function __construct(
        private string $file
    ) {}

    public function log(string $message): void {
        file_put_contents($this->file, $message . PHP_EOL, FILE_APPEND);
    }
}

$config = [
    LoggerInterface::class => FileLogger::class,
    FileLogger::class => function(ServiceContainer $container) {
        // You can use the container to resolve dependencies or configure the instance.
        return new FileLogger('/tmp/app.log');
    }
];

$container = new ServiceContainer($config);

// Retrieve a logger instance
$logger = $container->get(LoggerInterface::class);
$logger->log('Hello from PicoDI!');

var_dump($logger);