<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\CodeQuality\Rector\Identical;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp\Identical;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp\NotIdentical;
use Argtyper202511\PhpParser\Node\Expr\BooleanNot;
use Argtyper202511\PhpParser\Node\Expr\Instanceof_;
use Argtyper202511\PhpParser\Node\Name\FullyQualified;
use Argtyper202511\PHPStan\Type\ObjectType;
use Argtyper202511\Rector\PhpParser\Node\Value\ValueResolver;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Rector\StaticTypeMapper\ValueObject\Type\AliasedObjectType;
use Argtyper202511\Rector\StaticTypeMapper\ValueObject\Type\ShortenedObjectType;
use Argtyper202511\Rector\TypeDeclaration\TypeAnalyzer\NullableTypeAnalyzer;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\Identical\FlipTypeControlToUseExclusiveTypeRector\FlipTypeControlToUseExclusiveTypeRectorTest
 */
final class FlipTypeControlToUseExclusiveTypeRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\TypeAnalyzer\NullableTypeAnalyzer
     */
    private $nullableTypeAnalyzer;
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\Value\ValueResolver
     */
    private $valueResolver;
    public function __construct(NullableTypeAnalyzer $nullableTypeAnalyzer, ValueResolver $valueResolver)
    {
        $this->nullableTypeAnalyzer = $nullableTypeAnalyzer;
        $this->valueResolver = $valueResolver;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Flip type control from null compare to use exclusive instanceof object', [new CodeSample(<<<'CODE_SAMPLE'
function process(?DateTime $dateTime)
{
    if ($dateTime === null) {
        return;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
function process(?DateTime $dateTime)
{
    if (! $dateTime instanceof DateTime) {
        return;
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Identical::class, NotIdentical::class];
    }
    /**
     * @param Identical|NotIdentical $node
     */
    public function refactor(Node $node): ?Node
    {
        $expr = $this->matchNullComparedExpr($node);
        if (!$expr instanceof Expr) {
            return null;
        }
        $nullableObjectType = $this->nullableTypeAnalyzer->resolveNullableObjectType($expr);
        if (!$nullableObjectType instanceof ObjectType) {
            return null;
        }
        return $this->processConvertToExclusiveType($nullableObjectType, $expr, $node);
    }
    /**
     * @param \PhpParser\Node\Expr\BinaryOp\Identical|\PhpParser\Node\Expr\BinaryOp\NotIdentical $binaryOp
     * @return \PhpParser\Node\Expr\BooleanNot|\PhpParser\Node\Expr\Instanceof_
     */
    private function processConvertToExclusiveType(ObjectType $objectType, Expr $expr, $binaryOp)
    {
        $fullyQualifiedType = $objectType instanceof ShortenedObjectType || $objectType instanceof AliasedObjectType ? $objectType->getFullyQualifiedName() : $objectType->getClassName();
        $instanceof = new Instanceof_($expr, new FullyQualified($fullyQualifiedType));
        if ($binaryOp instanceof NotIdentical) {
            return $instanceof;
        }
        return new BooleanNot($instanceof);
    }
    /**
     * @param \PhpParser\Node\Expr\BinaryOp\Identical|\PhpParser\Node\Expr\BinaryOp\NotIdentical $binaryOp
     */
    private function matchNullComparedExpr($binaryOp): ?Expr
    {
        if ($this->valueResolver->isNull($binaryOp->left)) {
            return $binaryOp->right;
        }
        if ($this->valueResolver->isNull($binaryOp->right)) {
            return $binaryOp->left;
        }
        return null;
    }
}
