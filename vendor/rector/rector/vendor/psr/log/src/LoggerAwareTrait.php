<?php

namespace RectorPrefix202511\Psr\Log;

/**
 * Basic Implementation of LoggerAwareInterface.
 */
trait LoggerAwareTrait
{
    /**
     * The logger instance.
     * @var \RectorPrefix202511\Psr\Log\LoggerInterface|null
     */
    protected $logger;
    /**
     * Sets a logger.
     */
    public function setLogger(\RectorPrefix202511\Psr\Log\LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }
}
