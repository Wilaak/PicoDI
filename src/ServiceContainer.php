<?php

namespace Wilaak\PicoDI;

use Psr\Container\ContainerInterface;

use \ReflectionClass;
use \ReflectionNamedType;

class ServiceContainer implements ContainerInterface
{
    private array $instances = [];

    public function __construct(
        private array $config
    ) {}

    public function has(string $id): bool
    {
        return isset($this->config[$id])
            || isset($this->instances[$id]);
    }

    /**
     * @template T
     * @param class-string<T> $id
     * @return T
     */
    public function get(string $id): mixed
    {
        return $this->instances[$id] ??= isset($this->config[$id])
            ? $this->createInstanceFromConfig($id)
            : $this->autowireInstance($id);
    }

    private function autowireInstance(string $id): object
    {
        if (!class_exists($id)) {
            throw new ServiceNotFoundException("Service $id does not exist or is not configured in the container.");
        }

        $reflection = new ReflectionClass($id);
        $constructor = $reflection->getConstructor();
        if (!$constructor || $constructor->getNumberOfParameters() === 0) {
            return new $id();
        }

        $args = [];
        foreach ($constructor->getParameters() as $parameter) {
            $type = $parameter->getType();
            if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
                $dependencyClass = $type->getName();
                $args[] = $this->get($dependencyClass);
                continue;
            }
            if ($parameter->isDefaultValueAvailable()) {
                $args[] = $parameter->getDefaultValue();
                continue;
            }
            throw new ContainerException(
                "Could not resolve the dependency $" . $parameter->getName() . " for class '$id'" .
                    " Must either be a class type or have a default value."
            );
        }

        return $reflection->newInstanceArgs($args);
    }

    private function createInstanceFromConfig(string $id): mixed
    {
        $config = $this->config[$id];

        if (is_callable($config)) {
            return call_user_func($config, $this);
        }
        if (is_string($config)) {
            return $this->get($config);
        }

        throw new ContainerException(
            "Invalid service configuration for '$id': expected callable or string, got " .
                gettype($config) . "."
        );
    }
}
