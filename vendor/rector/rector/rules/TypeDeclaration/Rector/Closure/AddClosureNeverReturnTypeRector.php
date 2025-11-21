<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\TypeDeclaration\Rector\Closure;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr\Closure;
use Argtyper202511\Rector\PHPStan\ScopeFetcher;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Rector\TypeDeclaration\NodeManipulator\AddNeverReturnType;
use Argtyper202511\Rector\ValueObject\PhpVersionFeature;
use Argtyper202511\Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\Closure\AddClosureNeverReturnTypeRector\AddClosureNeverReturnTypeRectorTest
 */
final class AddClosureNeverReturnTypeRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\NodeManipulator\AddNeverReturnType
     */
    private $addNeverReturnType;
    public function __construct(AddNeverReturnType $addNeverReturnType)
    {
        $this->addNeverReturnType = $addNeverReturnType;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add "never" return-type for closure that never return anything', [new CodeSample(<<<'CODE_SAMPLE'
function () {
    throw new InvalidException();
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
function (): never {
    throw new InvalidException();
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Closure::class];
    }
    /**
     * @param Closure $node
     */
    public function refactor(Node $node): ?Node
    {
        $scope = ScopeFetcher::fetch($node);
        return $this->addNeverReturnType->add($node, $scope);
    }
    public function provideMinPhpVersion(): int
    {
        return PhpVersionFeature::NEVER_TYPE;
    }
}
