<?php

namespace Wilaak\PicoDI;

use Psr\Container\ContainerExceptionInterface;
use RuntimeException;
use Throwable;

class ContainerException extends RuntimeException implements ContainerExceptionInterface
{
    public function __construct(string $message, int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}