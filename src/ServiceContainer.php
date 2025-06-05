<?php

namespace Wilaak\PicoDI;

use Psr\Container\ContainerInterface;
use ReflectionClass;

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

        if (!class_exists($id)) {
            throw new ServiceNotFoundException(
                "Service '{$id}' could not be resolved: it is neither defined in the container configuration nor does a corresponding class exist. " .
                "Please check your service configuration and ensure the class '{$id}' exists and is autoloadable."
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
            if ($type && !$type->isBuiltin()) {
                $depClass = $type->getName();
                $params[] = $this->get($depClass);
            } elseif ($param->isDefaultValueAvailable()) {
                $params[] = $param->getDefaultValue();
            } else {
                throw new ContainerException(
                    "Unable to resolve the dependency '{$param->getName()}' for service '{$id}'. " .
                    "The parameter has no type hint or default value. " .
                    "Please provide a type hint, a default value, or configure this dependency explicitly in the container."
                );
            }
        }
        $instance = $reflection->newInstanceArgs($params);
        return $instance;
    }
}
