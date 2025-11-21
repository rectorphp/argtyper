<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\TypeDeclaration\Rector\FunctionLike;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\FunctionLike;
use Argtyper202511\PhpParser\Node\Stmt\ClassMethod;
use Argtyper202511\PhpParser\Node\Stmt\Function_;
use Argtyper202511\PHPStan\Type\Generic\GenericObjectType;
use Argtyper202511\PHPStan\Type\ObjectType;
use Argtyper202511\PHPStan\Type\Type;
use Argtyper202511\PHPStan\Type\TypeWithClassName;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\FunctionLike\AddParamTypeSplFixedArrayRector\AddParamTypeSplFixedArrayRectorTest
 */
final class AddParamTypeSplFixedArrayRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger
     */
    private $phpDocTypeChanger;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    /**
     * @var array<string, string>
     */
    private const SPL_FIXED_ARRAY_TO_SINGLE = ['Argtyper202511\PhpCsFixer\Tokenizer\Tokens' => 'Argtyper202511\PhpCsFixer\Tokenizer\Token', 'Argtyper202511\PhpCsFixer\Doctrine\Annotation\Tokens' => 'Argtyper202511\PhpCsFixer\Doctrine\Annotation\Token'];
    public function __construct(PhpDocTypeChanger $phpDocTypeChanger, PhpDocInfoFactory $phpDocInfoFactory)
    {
        $this->phpDocTypeChanger = $phpDocTypeChanger;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Function_::class, ClassMethod::class];
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add exact fixed array type in known cases', [new CodeSample(<<<'CODE_SAMPLE'
use PhpCsFixer\Tokenizer\Tokens;

class SomeClass
{
    public function run(Tokens $tokens)
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

class SomeClass
{
    /**
     * @param Tokens<Token>
     */
    public function run(Tokens $tokens)
    {
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @param FunctionLike $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node->getParams() === []) {
            return null;
        }
        $functionLikePhpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        $hasChanged = \false;
        foreach ($node->getParams() as $param) {
            if ($param->type === null) {
                continue;
            }
            $paramType = $this->nodeTypeResolver->getType($param->type);
            if ($paramType->isSuperTypeOf(new ObjectType('SplFixedArray'))->no()) {
                continue;
            }
            if (!$paramType instanceof TypeWithClassName) {
                continue;
            }
            if ($paramType instanceof GenericObjectType) {
                continue;
            }
            $genericParamType = $this->resolveGenericType($paramType);
            if (!$genericParamType instanceof Type) {
                continue;
            }
            $paramName = $this->getName($param);
            $changedParamType = $this->phpDocTypeChanger->changeParamType($node, $functionLikePhpDocInfo, $genericParamType, $param, $paramName);
            if ($changedParamType) {
                $hasChanged = \true;
            }
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    private function resolveGenericType(TypeWithClassName $typeWithClassName): ?\Argtyper202511\PHPStan\Type\Generic\GenericObjectType
    {
        foreach (self::SPL_FIXED_ARRAY_TO_SINGLE as $fixedArrayClass => $singleClass) {
            if ($typeWithClassName->getClassName() === $fixedArrayClass) {
                $genericObjectType = new ObjectType($singleClass);
                return new GenericObjectType($typeWithClassName->getClassName(), [$genericObjectType]);
            }
        }
        return null;
    }
}
