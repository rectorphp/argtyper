<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Php71\Rector\TryCatch;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Stmt\TryCatch;
use Argtyper202511\Rector\PhpParser\Printer\BetterStandardPrinter;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Rector\ValueObject\PhpVersionFeature;
use Argtyper202511\Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Php71\Rector\TryCatch\MultiExceptionCatchRector\MultiExceptionCatchRectorTest
 */
final class MultiExceptionCatchRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \Rector\PhpParser\Printer\BetterStandardPrinter
     */
    private $betterStandardPrinter;
    public function __construct(BetterStandardPrinter $betterStandardPrinter)
    {
        $this->betterStandardPrinter = $betterStandardPrinter;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change multiple catch statements of the same exception to a single one `|` separated', [new CodeSample(<<<'CODE_SAMPLE'
try {
    // Some code...
} catch (ExceptionType1 $exception) {
    $sameCode;
} catch (ExceptionType2 $exception) {
    $sameCode;
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
try {
    // Some code...
} catch (ExceptionType1 | ExceptionType2 $exception) {
    $sameCode;
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [TryCatch::class];
    }
    /**
     * @param TryCatch $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (\count($node->catches) < 2) {
            return null;
        }
        $hasChanged = \false;
        foreach ($node->catches as $key => $catch) {
            if (!isset($node->catches[$key + 1])) {
                break;
            }
            $currentPrintedCatch = $this->betterStandardPrinter->print($catch->stmts);
            $nextPrintedCatch = $this->betterStandardPrinter->print($node->catches[$key + 1]->stmts);
            // already duplicated catch → remove it and join the type
            if ($currentPrintedCatch === $nextPrintedCatch) {
                // use current var as next var
                $node->catches[$key + 1]->var = $node->catches[$key]->var;
                // merge next types as current merge to next types
                $node->catches[$key + 1]->types = \array_merge($node->catches[$key]->types, $node->catches[$key + 1]->types);
                unset($node->catches[$key]);
                $hasChanged = \true;
            }
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::MULTI_EXCEPTION_CATCH;
    }
}
