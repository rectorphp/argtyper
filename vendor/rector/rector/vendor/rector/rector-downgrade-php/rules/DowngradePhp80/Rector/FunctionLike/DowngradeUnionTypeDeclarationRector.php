<?php

declare (strict_types=1);
namespace Rector\DowngradePhp80\Rector\FunctionLike;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr\ArrowFunction;
use Argtyper202511\PhpParser\Node\Expr\Closure;
use Argtyper202511\PhpParser\Node\Stmt\ClassMethod;
use Argtyper202511\PhpParser\Node\Stmt\Function_;
use Argtyper202511\PhpParser\Node\UnionType;
use Rector\PhpDocDecorator\PhpDocFromTypeDeclarationDecorator;
use Rector\Rector\AbstractRector;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DowngradePhp80\Rector\FunctionLike\DowngradeUnionTypeDeclarationRector\DowngradeUnionTypeDeclarationRectorTest
 */
final class DowngradeUnionTypeDeclarationRector extends AbstractRector
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
    public function getNodeTypes(): array
    {
        return [Function_::class, ClassMethod::class, Closure::class, ArrowFunction::class];
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Remove the union type params and returns, add @param/@return tags instead', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function echoInput(string|int $input): int|bool
    {
        echo $input;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    /**
     * @param string|int $input
     * @return int|bool
     */
    public function echoInput($input)
    {
        echo $input;
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @param ClassMethod|Closure|Function_|ArrowFunction $node
     */
    public function refactor(Node $node): ?Node
    {
        $paramDecorated = \false;
        foreach ($node->getParams() as $param) {
            if (!$param->type instanceof UnionType) {
                continue;
            }
            $this->phpDocFromTypeDeclarationDecorator->decorateParam($param, $node, [\Argtyper202511\PHPStan\Type\UnionType::class]);
            $paramDecorated = \true;
        }
        if (!$node->returnType instanceof UnionType) {
            if ($paramDecorated) {
                return $node;
            }
            return null;
        }
        $this->phpDocFromTypeDeclarationDecorator->decorateReturn($node);
        return $node;
    }
}
