<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\CodingStyle\Rector\Stmt;

use Argtyper202511\RectorPrefix202511\Nette\Utils\Strings;
use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Identifier;
use Argtyper202511\PhpParser\Node\Stmt\Namespace_;
use Argtyper202511\PhpParser\Node\Stmt\Use_;
use Argtyper202511\Rector\PhpParser\Node\CustomNode\FileWithoutNamespace;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodingStyle\Rector\Stmt\RemoveUselessAliasInUseStatementRector\RemoveUselessAliasInUseStatementRectorTest
 */
final class RemoveUselessAliasInUseStatementRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Remove useless alias in use statement as same name with last use statement name', [new CodeSample(<<<'CODE_SAMPLE'
use App\Bar as Bar;
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use App\Bar;
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [FileWithoutNamespace::class, Namespace_::class];
    }
    /**
     * @param FileWithoutNamespace|Namespace_ $node
     * @return null|\Rector\PhpParser\Node\CustomNode\FileWithoutNamespace|\PhpParser\Node\Stmt\Namespace_
     */
    public function refactor(Node $node)
    {
        $hasChanged = \false;
        foreach ($node->stmts as $stmt) {
            if (!$stmt instanceof Use_) {
                continue;
            }
            if (count($stmt->uses) !== 1) {
                continue;
            }
            if (!isset($stmt->uses[0])) {
                continue;
            }
            $aliasName = $stmt->uses[0]->alias instanceof Identifier ? $stmt->uses[0]->alias->toString() : null;
            if ($aliasName === null) {
                continue;
            }
            $useName = $stmt->uses[0]->name->toString();
            $lastName = Strings::after($useName, '\\', -1) ?? $useName;
            if ($lastName === $aliasName) {
                $stmt->uses[0]->alias = null;
                $hasChanged = \true;
            }
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
}
