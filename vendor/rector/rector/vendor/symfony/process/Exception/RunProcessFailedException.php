<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix202511\Symfony\Component\Process\Exception;

use RectorPrefix202511\Symfony\Component\Process\Messenger\RunProcessContext;
/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class RunProcessFailedException extends \RectorPrefix202511\Symfony\Component\Process\Exception\RuntimeException
{
    /**
     * @readonly
     * @var \RectorPrefix202511\Symfony\Component\Process\Messenger\RunProcessContext
     */
    public $context;
    public function __construct(\RectorPrefix202511\Symfony\Component\Process\Exception\ProcessFailedException $exception, RunProcessContext $context)
    {
        $this->context = $context;
        parent::__construct($exception->getMessage(), $exception->getCode());
    }
}
