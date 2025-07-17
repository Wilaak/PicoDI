<?php

namespace Wilaak\PicoDI;

use Psr\Container\NotFoundExceptionInterface;

class ServiceNotFoundException extends ContainerException implements NotFoundExceptionInterface
{
}