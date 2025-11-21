<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\CodingStyle\Rector\ClassConst;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Const_;
use Argtyper202511\PhpParser\Node\Stmt\ClassConst;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodingStyle\Rector\ClassConst\SplitGroupedClassConstantsRector\SplitGroupedClassConstantsRectorTest
 */
final class SplitGroupedClassConstantsRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Separate class constant to own lines', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    const HI = true, HELLO = 'true';
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    const HI = true;
    const HELLO = 'true';
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [ClassConst::class];
    }
    /**
     * @param ClassConst $node
     * @return ClassConst[]|null
     */
    public function refactor(Node $node): ?array
    {
        if (count($node->consts) < 2) {
            return null;
        }
        /** @var Const_[] $allConsts */
        $allConsts = $node->consts;
        /** @var Const_ $firstConst */
        $firstConst = array_shift($allConsts);
        $node->consts = [$firstConst];
        $nextClassConsts = $this->createNextClassConsts($allConsts, $node);
        return array_merge([$node], $nextClassConsts);
    }
    /**
     * @param Const_[] $consts
     * @return ClassConst[]
     */
    private function createNextClassConsts(array $consts, ClassConst $classConst): array
    {
        $decoratedConsts = [];
        foreach ($consts as $const) {
            $decoratedConsts[] = new ClassConst([$const], $classConst->flags, $classConst->getAttributes());
        }
        return $decoratedConsts;
    }
}
