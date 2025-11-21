<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\DowngradePhp74\Rector\Identical;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp\Identical;
use Argtyper202511\PhpParser\Node\Expr\BooleanNot;
use Argtyper202511\PhpParser\Node\Expr\FuncCall;
use Argtyper202511\Rector\PhpParser\Node\Value\ValueResolver;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DowngradePhp74\Rector\Identical\DowngradeFreadFwriteFalsyToNegationRector\DowngradeFreadFwriteFalsyToNegationRectorTest
 */
final class DowngradeFreadFwriteFalsyToNegationRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\Value\ValueResolver
     */
    private $valueResolver;
    /**
     * @var string[]
     */
    private const FUNC_FREAD_FWRITE = ['fread', 'fwrite'];
    public function __construct(ValueResolver $valueResolver)
    {
        $this->valueResolver = $valueResolver;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Changes fread() or fwrite() compare to false to negation check', [new CodeSample(<<<'CODE_SAMPLE'
fread($handle, $length) === false;
fwrite($fp, '1') === false;
CODE_SAMPLE
, <<<'CODE_SAMPLE'
!fread($handle, $length);
!fwrite($fp, '1');
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Identical::class];
    }
    /**
     * @param Identical $node
     */
    public function refactor(Node $node) : ?Node
    {
        $compareExpr = $this->getCompareValue($node);
        if (!$compareExpr instanceof Expr) {
            return null;
        }
        if (!$this->valueResolver->isFalse($compareExpr)) {
            return null;
        }
        return new BooleanNot($this->getFunction($node));
    }
    private function getCompareValue(Identical $identical) : ?Expr
    {
        if ($identical->left instanceof FuncCall && $this->isNames($identical->left, self::FUNC_FREAD_FWRITE)) {
            return $identical->right;
        }
        if (!$identical->right instanceof FuncCall) {
            return null;
        }
        if (!$this->isNames($identical->right, self::FUNC_FREAD_FWRITE)) {
            return null;
        }
        return $identical->left;
    }
    private function getFunction(Identical $identical) : FuncCall
    {
        /** @var FuncCall $funcCall */
        $funcCall = $identical->left instanceof FuncCall ? $identical->left : $identical->right;
        return $funcCall;
    }
}
