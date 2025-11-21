<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\DowngradePhp81\Rector\FunctionLike;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr\Closure;
use Argtyper202511\PhpParser\Node\Stmt\ClassMethod;
use Argtyper202511\PhpParser\Node\Stmt\Function_;
use Argtyper202511\PHPStan\Type\NeverType;
use Argtyper202511\Rector\PhpDocDecorator\PhpDocFromTypeDeclarationDecorator;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://wiki.php.net/rfc/noreturn_type
 *
 * @see \Rector\Tests\DowngradePhp81\Rector\FunctionLike\DowngradeNeverTypeDeclarationRector\DowngradeNeverTypeDeclarationRectorTest
 */
final class DowngradeNeverTypeDeclarationRector extends AbstractRector
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
        return [Function_::class, ClassMethod::class, Closure::class];
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Remove "never" return type, add a "@return never" tag instead', [new CodeSample(<<<'CODE_SAMPLE'
function someFunction(): never
{
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
/**
 * @return never
 */
function someFunction()
{
}
CODE_SAMPLE
)]);
    }
    /**
     * @param ClassMethod|Closure|Function_ $node
     */
    public function refactor(Node $node): ?Node
    {
        $neverType = new NeverType();
        if (!$this->phpDocFromTypeDeclarationDecorator->decorateReturnWithSpecificType($node, $neverType)) {
            return null;
        }
        return $node;
    }
}
