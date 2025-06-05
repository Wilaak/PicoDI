<?php

namespace Wilaak\PicoDI;

use Psr\Container\ContainerExceptionInterface;
use RuntimeException;

class ContainerException extends RuntimeException implements ContainerExceptionInterface
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}