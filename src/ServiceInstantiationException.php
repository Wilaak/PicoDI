<?php

namespace Wilaak\PicoDI;

use Throwable;

class ServiceInstantiationException extends ContainerException
{
    public function __construct(string $message, int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message);
    }
}
