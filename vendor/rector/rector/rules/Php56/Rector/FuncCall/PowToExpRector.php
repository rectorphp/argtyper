<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Php56\Rector\FuncCall;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp\Pow;
use Argtyper202511\PhpParser\Node\Expr\FuncCall;
use Argtyper202511\PhpParser\Node\Expr\UnaryMinus;
use Argtyper202511\Rector\NodeTypeResolver\Node\AttributeKey;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Rector\ValueObject\PhpVersionFeature;
use Argtyper202511\Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Php56\Rector\FuncCall\PowToExpRector\PowToExpRectorTest
 */
final class PowToExpRector extends AbstractRector implements MinPhpVersionInterface
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Changes `pow(val, val2)` to `**` (exp) parameter', [new CodeSample('pow(1, 2);', '1**2;')]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [FuncCall::class];
    }
    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (!$this->isName($node, 'pow')) {
            return null;
        }
        if ($node->isFirstClassCallable()) {
            return null;
        }
        $firstExpr = $node->getArgs()[0]->value;
        $secondExpr = $node->getArgs()[1]->value;
        if ($firstExpr instanceof UnaryMinus) {
            $firstExpr->setAttribute(AttributeKey::ORIGINAL_NODE, null);
        }
        return new Pow($firstExpr, $secondExpr);
    }
    public function provideMinPhpVersion(): int
    {
        return PhpVersionFeature::EXP_OPERATOR;
    }
}
