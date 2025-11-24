<?php

namespace RectorPrefix202511\Psr\Log;

/**
 * Describes a logger-aware instance.
 */
interface LoggerAwareInterface
{
    /**
     * Sets a logger instance on the object.
     */
    public function setLogger(\RectorPrefix202511\Psr\Log\LoggerInterface $logger): void;
}
