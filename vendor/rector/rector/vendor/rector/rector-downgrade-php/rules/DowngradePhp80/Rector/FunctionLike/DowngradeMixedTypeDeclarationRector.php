<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\DowngradePhp80\Rector\FunctionLike;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr\ArrowFunction;
use Argtyper202511\PhpParser\Node\Expr\Closure;
use Argtyper202511\PhpParser\Node\Stmt\ClassMethod;
use Argtyper202511\PhpParser\Node\Stmt\Function_;
use Argtyper202511\PHPStan\Type\MixedType;
use Argtyper202511\Rector\PhpDocDecorator\PhpDocFromTypeDeclarationDecorator;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DowngradePhp80\Rector\FunctionLike\DowngradeMixedTypeDeclarationRector\DowngradeMixedTypeDeclarationRectorTest
 */
final class DowngradeMixedTypeDeclarationRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\PhpDocDecorator\PhpDocFromTypeDeclarationDecorator
     */
    private $phpDocFromTypeDeclarationDecorator;
    public function __construct(PhpDocFromTypeDeclarationDecorator $phpDocFromTypeDeclarationDecorator)
    {
        $this->phpDocFromTypeDeclarationDecorator = $phpDocFromTypeDeclarationDecorator;
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Function_::class, ClassMethod::class, Closure::class, ArrowFunction::class];
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Remove the "mixed" param and return type, add a @param and @return tag instead', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function someFunction(mixed $anything): mixed
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    /**
     * @param mixed $anything
     * @return mixed
     */
    public function someFunction($anything)
    {
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @param ClassMethod|Function_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        $mixedType = new MixedType();
        $hasChanged = \false;
        $hasParamChanged = \false;
        foreach ($node->getParams() as $param) {
            $hasParamChanged = $this->phpDocFromTypeDeclarationDecorator->decorateParamWithSpecificType($param, $node, $mixedType);
            if ($hasParamChanged) {
                $hasChanged = \true;
            }
        }
        if (!$this->phpDocFromTypeDeclarationDecorator->decorateReturnWithSpecificType($node, $mixedType)) {
            if ($hasChanged) {
                return $node;
            }
            return null;
        }
        return $node;
    }
}
