<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\CodeQuality\Rector\FuncCall;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Arg;
use Argtyper202511\PhpParser\Node\Expr\FuncCall;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\FuncCall\IsAWithStringWithThirdArgumentRector\IsAWithStringWithThirdArgumentRectorTest
 */
final class IsAWithStringWithThirdArgumentRector extends AbstractRector
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Complete missing 3rd argument in case is_a() function in case of strings', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function __construct(string $value)
    {
        return is_a($value, 'stdClass');
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function __construct(string $value)
    {
        return is_a($value, 'stdClass', true);
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [FuncCall::class];
    }
    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->isName($node, 'is_a')) {
            return null;
        }
        if ($node->isFirstClassCallable()) {
            return null;
        }
        if (isset($node->getArgs()[2])) {
            return null;
        }
        $firstArg = $node->getArgs()[0];
        $firstArgumentStaticType = $this->getType($firstArg->value);
        if (!$firstArgumentStaticType->isString()->yes()) {
            return null;
        }
        $node->args[2] = new Arg($this->nodeFactory->createTrue());
        return $node;
    }
}
