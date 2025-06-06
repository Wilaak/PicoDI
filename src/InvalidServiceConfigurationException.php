<?php

namespace Wilaak\PicoDI;

use Throwable;

class InvalidServiceConfigurationException extends ContainerException
{
    public function __construct(string $message, int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}