<?php

declare (strict_types=1);
namespace Argtyper202511\Symplify\RuleDocGenerator\Contract;

use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
interface RuleCodeSamplePrinterInterface
{
    public function isMatch(string $class): bool;
    /**
     * @return string[]
     */
    public function print(\Argtyper202511\Symplify\RuleDocGenerator\Contract\CodeSampleInterface $codeSample, RuleDefinition $ruleDefinition): array;
}
