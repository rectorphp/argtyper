<?php

declare (strict_types=1);
namespace Argtyper202511\PhpParser;

interface ErrorHandler
{
    /**
     * Handle an error generated during lexing, parsing or some other operation.
     *
     * @param Error $error The error that needs to be handled
     */
    public function handleError(\Argtyper202511\PhpParser\Error $error) : void;
}
