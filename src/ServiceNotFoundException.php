<?php

namespace Wilaak\PicoDI;

use Psr\Container\NotFoundExceptionInterface;

class ServiceNotFoundException extends ContainerException implements NotFoundExceptionInterface
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}