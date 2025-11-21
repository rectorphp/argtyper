<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Php85\Rector\ClassMethod;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Expr\Array_;
use Argtyper202511\PhpParser\Node\Expr\Closure;
use Argtyper202511\PhpParser\Node\Stmt\Class_;
use Argtyper202511\PhpParser\Node\Stmt\ClassMethod;
use Argtyper202511\PhpParser\Node\Stmt\Function_;
use Argtyper202511\PhpParser\Node\Stmt\Return_;
use Argtyper202511\PhpParser\NodeVisitor;
use Argtyper202511\Rector\PhpParser\Node\Value\ValueResolver;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Rector\ValueObject\PhpVersionFeature;
use Argtyper202511\Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://wiki.php.net/rfc/deprecations_php_8_5#deprecate_debuginfo_returning_null
 * @see \Rector\Tests\Php85\Rector\MethodCall\NullDebugInfoReturnRector\NullDebugInfoReturnRectorTest
 */
final class NullDebugInfoReturnRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\Value\ValueResolver
     */
    private $valueResolver;
    public function __construct(ValueResolver $valueResolver)
    {
        $this->valueResolver = $valueResolver;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Replaces `null` return value with empty array in `__debugInfo` methods', [new CodeSample(<<<'CODE_SAMPLE'
new class
{
    public function __debugInfo() {
        return null;
    }
};
CODE_SAMPLE
, <<<'CODE_SAMPLE'
new class
{
    public function __debugInfo() {
        return [];
    }
};
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        if (!$this->isName($node, '__debugInfo')) {
            return null;
        }
        $hasChanged = \false;
        $this->traverseNodesWithCallable((array) $node->stmts, function (Node $node) use (&$hasChanged) {
            if ($node instanceof Class_ || $node instanceof Function_ || $node instanceof Closure) {
                return NodeVisitor::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
            }
            if ($node instanceof Return_ && (!$node->expr instanceof Expr || $this->valueResolver->isNull($node->expr))) {
                $hasChanged = \true;
                $node->expr = new Array_();
                return $node;
            }
            return null;
        });
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    public function provideMinPhpVersion(): int
    {
        return PhpVersionFeature::DEPRECATED_NULL_DEBUG_INFO_RETURN;
    }
}
