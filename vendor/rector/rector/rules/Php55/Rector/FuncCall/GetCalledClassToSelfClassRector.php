<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Php55\Rector\FuncCall;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr\FuncCall;
use Argtyper202511\PHPStan\Analyser\Scope;
use Argtyper202511\Rector\Enum\ObjectReference;
use Argtyper202511\Rector\NodeTypeResolver\Node\AttributeKey;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Rector\Reflection\ClassModifierChecker;
use Argtyper202511\Rector\ValueObject\PhpVersionFeature;
use Argtyper202511\Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Php55\Rector\FuncCall\GetCalledClassToSelfClassRector\GetCalledClassToSelfClassRectorTest
 */
final class GetCalledClassToSelfClassRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \Rector\Reflection\ClassModifierChecker
     */
    private $classModifierChecker;
    public function __construct(ClassModifierChecker $classModifierChecker)
    {
        $this->classModifierChecker = $classModifierChecker;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change `get_called_class()` to `self::class` on final class', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
   public function callOnMe()
   {
       var_dump(get_called_class());
   }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
   public function callOnMe()
   {
       var_dump(self::class);
   }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [FuncCall::class];
    }
    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (!$this->isName($node, 'get_called_class')) {
            return null;
        }
        $scope = $node->getAttribute(AttributeKey::SCOPE);
        if (!$scope instanceof Scope) {
            return null;
        }
        if (!$scope->isInClass()) {
            return null;
        }
        if ($this->classModifierChecker->isInsideFinalClass($node)) {
            return $this->nodeFactory->createClassConstFetch(ObjectReference::SELF, 'class');
        }
        if ($scope->isInAnonymousFunction()) {
            return $this->nodeFactory->createClassConstFetch(ObjectReference::SELF, 'class');
        }
        return null;
    }
    public function provideMinPhpVersion(): int
    {
        return PhpVersionFeature::CLASSNAME_CONSTANT;
    }
}
