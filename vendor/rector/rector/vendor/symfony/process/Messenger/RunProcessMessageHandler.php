<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix202511\Symfony\Component\Process\Messenger;

use RectorPrefix202511\Symfony\Component\Process\Exception\ProcessFailedException;
use RectorPrefix202511\Symfony\Component\Process\Exception\RunProcessFailedException;
use RectorPrefix202511\Symfony\Component\Process\Process;
/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class RunProcessMessageHandler
{
    public function __invoke(\RectorPrefix202511\Symfony\Component\Process\Messenger\RunProcessMessage $message): \RectorPrefix202511\Symfony\Component\Process\Messenger\RunProcessContext
    {
        $process = new Process($message->command, $message->cwd, $message->env, $message->input, $message->timeout);
        try {
            return new \RectorPrefix202511\Symfony\Component\Process\Messenger\RunProcessContext($message, $process->mustRun());
        } catch (ProcessFailedException $e) {
            throw new RunProcessFailedException($e, new \RectorPrefix202511\Symfony\Component\Process\Messenger\RunProcessContext($message, $e->getProcess()));
        }
    }
}
