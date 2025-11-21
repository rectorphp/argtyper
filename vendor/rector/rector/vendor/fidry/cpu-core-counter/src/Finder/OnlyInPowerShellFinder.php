<?php

/*
 * This file is part of the Fidry CPUCounter Config package.
 *
 * (c) Théo FIDRY <theo.fidry@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare (strict_types=1);
namespace RectorPrefix202511\Fidry\CpuCoreCounter\Finder;

use function getenv;
use function sprintf;
final class OnlyInPowerShellFinder implements \RectorPrefix202511\Fidry\CpuCoreCounter\Finder\CpuCoreFinder
{
    /**
     * @var CpuCoreFinder
     */
    private $decoratedFinder;
    public function __construct(\RectorPrefix202511\Fidry\CpuCoreCounter\Finder\CpuCoreFinder $decoratedFinder)
    {
        $this->decoratedFinder = $decoratedFinder;
    }
    public function diagnose(): string
    {
        $powerShellModulePath = getenv('PSModulePath');
        return $this->skip() ? sprintf('Skipped; no power shell module path detected ("%s").', $powerShellModulePath) : $this->decoratedFinder->diagnose();
    }
    public function find(): ?int
    {
        return $this->skip() ? null : $this->decoratedFinder->find();
    }
    public function toString(): string
    {
        return sprintf('OnlyInPowerShellFinder(%s)', $this->decoratedFinder->toString());
    }
    private function skip(): bool
    {
        return \false === getenv('PSModulePath');
    }
}
