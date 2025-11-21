<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\TypeDeclaration\Rector\Empty_;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr\ArrayDimFetch;
use Argtyper202511\PhpParser\Node\Expr\BooleanNot;
use Argtyper202511\PhpParser\Node\Expr\Empty_;
use Argtyper202511\PhpParser\Node\Expr\Instanceof_;
use Argtyper202511\PhpParser\Node\Name;
use Argtyper202511\PHPStan\Type\ObjectType;
use Argtyper202511\PHPStan\Type\TypeCombinator;
use Argtyper202511\PHPStan\Type\UnionType;
use Argtyper202511\Rector\PHPStan\ScopeFetcher;
use Argtyper202511\Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Rector\StaticTypeMapper\StaticTypeMapper;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\Empty_\EmptyOnNullableObjectToInstanceOfRector\EmptyOnNullableObjectToInstanceOfRectorTest
 */
final class EmptyOnNullableObjectToInstanceOfRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\StaticTypeMapper\StaticTypeMapper
     */
    private $staticTypeMapper;
    public function __construct(StaticTypeMapper $staticTypeMapper)
    {
        $this->staticTypeMapper = $staticTypeMapper;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change `empty()` on nullable object to instanceof check', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run(?AnotherObject $anotherObject)
    {
        if (empty($anotherObject)) {
            return false;
        }

        return true;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run(?AnotherObject $anotherObject)
    {
        if (! $anotherObject instanceof AnotherObject) {
            return false;
        }

        return true;
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
        return [Empty_::class, BooleanNot::class];
    }
    /**
     * @param Empty_|BooleanNot $node
     * @return null|\PhpParser\Node\Expr\Instanceof_|\PhpParser\Node\Expr\BooleanNot
     */
    public function refactor(Node $node)
    {
        if ($node instanceof BooleanNot) {
            if (!$node->expr instanceof Empty_) {
                return null;
            }
            $isNegated = \true;
            $empty = $node->expr;
        } else {
            $empty = $node;
            $isNegated = \false;
        }
        if ($empty->expr instanceof ArrayDimFetch) {
            return null;
        }
        $scope = ScopeFetcher::fetch($node);
        $exprType = $scope->getNativeType($empty->expr);
        if (!$exprType instanceof UnionType) {
            return null;
        }
        $exprType = TypeCombinator::removeNull($exprType);
        if (!$exprType instanceof ObjectType) {
            return null;
        }
        $objectType = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($exprType, TypeKind::RETURN);
        if (!$objectType instanceof Name) {
            return null;
        }
        $instanceof = new Instanceof_($empty->expr, $objectType);
        if ($isNegated) {
            return $instanceof;
        }
        return new BooleanNot($instanceof);
    }
}
