<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\TypeDeclaration\Rector\Closure;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr\Closure;
use Argtyper202511\PhpParser\Node\Identifier;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Rector\TypeDeclaration\TypeInferer\SilentVoidResolver;
use Argtyper202511\Rector\ValueObject\PhpVersionFeature;
use Argtyper202511\Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\Closure\AddClosureVoidReturnTypeWhereNoReturnRector\AddClosureVoidReturnTypeWhereNoReturnRectorTest
 */
final class AddClosureVoidReturnTypeWhereNoReturnRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\TypeInferer\SilentVoidResolver
     */
    private $silentVoidResolver;
    public function __construct(SilentVoidResolver $silentVoidResolver)
    {
        $this->silentVoidResolver = $silentVoidResolver;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Add closure return type void if there is no return', [new CodeSample(<<<'CODE_SAMPLE'
function () {
};
CODE_SAMPLE
, <<<'CODE_SAMPLE'
function (): void {
};
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Closure::class];
    }
    /**
     * @param Closure $node
     */
    public function refactor(Node $node) : ?Node
    {
        // already has return type → skip
        if ($node->returnType instanceof Node) {
            return null;
        }
        if (!$this->silentVoidResolver->hasExclusiveVoid($node)) {
            return null;
        }
        $node->returnType = new Identifier('void');
        return $node;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::VOID_TYPE;
    }
}
