<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Php74\Rector\FuncCall;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Arg;
use Argtyper202511\PhpParser\Node\Expr\FuncCall;
use Argtyper202511\PhpParser\Node\Name;
use Argtyper202511\PhpParser\Node\Scalar\String_;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Rector\ValueObject\PhpVersionFeature;
use Argtyper202511\Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://3v4l.org/Kcs98
 * @see \Rector\Tests\Php74\Rector\FuncCall\RestoreIncludePathToIniRestoreRector\RestoreIncludePathToIniRestoreRectorTest
 */
final class RestoreIncludePathToIniRestoreRector extends AbstractRector implements MinPhpVersionInterface
{
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::DEPRECATE_RESTORE_INCLUDE_PATH;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change restore_include_path() to ini_restore("include_path")', [new CodeSample(<<<'CODE_SAMPLE'
restore_include_path();
CODE_SAMPLE
, <<<'CODE_SAMPLE'
ini_restore('include_path');
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [FuncCall::class];
    }
    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node) : ?FuncCall
    {
        if (!$this->isName($node, 'restore_include_path')) {
            return null;
        }
        if ($node->isFirstClassCallable()) {
            return null;
        }
        $node->name = new Name('ini_restore');
        $node->args[0] = new Arg(new String_('include_path'));
        return $node;
    }
}
