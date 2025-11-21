<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\TypeDeclaration\Rector\ArrowFunction;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr\ArrowFunction;
use Argtyper202511\PHPStan\Type\MixedType;
use Argtyper202511\PHPStan\Type\NullType;
use Argtyper202511\Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Rector\StaticTypeMapper\StaticTypeMapper;
use Argtyper202511\Rector\ValueObject\PhpVersionFeature;
use Argtyper202511\Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\ArrowFunction\AddArrowFunctionReturnTypeRector\AddArrowFunctionReturnTypeRectorTest
 */
final class AddArrowFunctionReturnTypeRector extends AbstractRector implements MinPhpVersionInterface
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
        return new RuleDefinition('Add known return type to arrow function', [new CodeSample(<<<'CODE_SAMPLE'
fn () => [];
CODE_SAMPLE
, <<<'CODE_SAMPLE'
fn (): array => [];
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [ArrowFunction::class];
    }
    /**
     * @param ArrowFunction $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($node->returnType instanceof Node) {
            return null;
        }
        $type = $this->nodeTypeResolver->getNativeType($node->expr);
        // not valid to add explicit type in PHP
        if ($type->isVoid()->yes()) {
            return null;
        }
        $docblockType = $this->getType($node->expr);
        if ($type instanceof MixedType && $docblockType instanceof NullType) {
            return null;
        }
        $returnTypeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($type, TypeKind::RETURN);
        if (!$returnTypeNode instanceof Node) {
            return null;
        }
        $node->returnType = $returnTypeNode;
        return $node;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::ARROW_FUNCTION;
    }
}
