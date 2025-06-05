<?php

namespace Wilaak\PicoDI;

use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionNamedType;
use Throwable;

class ServiceContainer implements ContainerInterface
{
    private array $instances = [];

    public function __construct(
        private array $config
    ) {}

    public function has(string $id): bool
    {
        return array_key_exists($id, $this->config) || array_key_exists($id, $this->instances);
    }

    public function get(string $id)
    {
        $this->instances[$id] ??= $this->instantiateService($id);
        return $this->instances[$id];
    }

    private function instantiateService(string $id)
    {
        if ($this->has($id)) {
            $config = $this->config[$id];

            if (is_callable($config)) {
                // The config is a factory function.
                return call_user_func($config);
            }

            if (is_string($config)) {
                // This is an alias.
                return $this->get($config);
            }

            if (is_array($config)) {
                // Get the listed dependencies from the container.
                $args = array_map(
                    fn($dependency) =>
                    is_callable($dependency) ?
                        call_user_func($dependency) :
                        $this->get($dependency),
                    $config
                );
                return new $id(...$args);
            }

            throw new ContainerException(
                "Invalid service configuration for '{$id}': expected callable, string, or array, got " . gettype($config) . ". " .
                    "Please check your service configuration."
            );
        }

        try {
            $class_exists = class_exists($id);
        } catch (Throwable $exception) {
            throw new ContainerException(
                "An error occurred while trying to load the service '{$id}': " . $exception->getMessage() . ". " .
                    "Please check your service configuration and ensure the class '{$id}' exists and is autoloadable."
            );
        }

        if (!$class_exists) {
            throw new ServiceNotFoundException(
                "Service '{$id}' could not be resolved: the class '{$id}' does not exist or is not autoloadable. " .
                    "Please check your service configuration and ensure the class '{$id}' exists and is properly autoloadable."
            );
        }

        $reflection = new ReflectionClass($id);
        $constructor = $reflection->getConstructor();
        if (!$constructor || $constructor->getNumberOfParameters() === 0) {
            $instance = new $id();
            return $instance;
        }
        $params = [];
        foreach ($constructor->getParameters() as $param) {
            $type = $param->getType();
            if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
                // Handle single class type
                $params[] = $this->get(
                    $type->getName()
                );
            } elseif ($param->isDefaultValueAvailable()) {
                // Use default value if available
                $params[] = $param->getDefaultValue();
            } else {
                throw new ContainerException(
                    "Unable to resolve the dependency '{$param->getName()}' for service '{$id}'. " .
                        "The parameter has no resolvable type hint, default value, or explicit configuration in the container."
                );
            }
        }
        $instance = $reflection->newInstanceArgs($params);
        return $instance;
    }
}
