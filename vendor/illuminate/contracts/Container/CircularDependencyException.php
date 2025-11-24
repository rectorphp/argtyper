<?php

namespace Argtyper202511\Illuminate\Contracts\Container;

use Exception;
use Argtyper202511\Psr\Container\ContainerExceptionInterface;
class CircularDependencyException extends Exception implements ContainerExceptionInterface
{
    //
}
