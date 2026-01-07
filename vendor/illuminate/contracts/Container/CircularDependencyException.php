<?php

namespace Argtyper202601\Illuminate\Contracts\Container;

use Exception;
use Argtyper202601\Psr\Container\ContainerExceptionInterface;
class CircularDependencyException extends Exception implements ContainerExceptionInterface
{
    //
}
