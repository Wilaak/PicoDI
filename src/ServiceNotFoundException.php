<?php

namespace Wilaak\PicoDI;

use Psr\Container\NotFoundExceptionInterface;
use Throwable;

class ServiceNotFoundException extends ContainerException implements NotFoundExceptionInterface
{
    public function __construct(string $message, int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message);
    }
}