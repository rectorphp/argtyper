<?php

namespace RectorPrefix202512\Psr\Log;

/**
 * Basic Implementation of LoggerAwareInterface.
 */
trait LoggerAwareTrait
{
    /**
     * The logger instance.
     * @var \RectorPrefix202512\Psr\Log\LoggerInterface|null
     */
    protected $logger;
    /**
     * Sets a logger.
     */
    public function setLogger(\RectorPrefix202512\Psr\Log\LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }
}
