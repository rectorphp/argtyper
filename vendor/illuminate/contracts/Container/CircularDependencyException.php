<?php

namespace SherlockTypes202308\Illuminate\Contracts\Container;

use Exception;
use SherlockTypes202308\Psr\Container\ContainerExceptionInterface;
class CircularDependencyException extends Exception implements ContainerExceptionInterface
{
    //
}
