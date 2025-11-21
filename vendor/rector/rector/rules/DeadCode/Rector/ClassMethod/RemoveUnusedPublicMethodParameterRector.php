<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\ClassMethod;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Name\FullyQualified;
use Argtyper202511\PhpParser\Node\Stmt\Class_;
use Argtyper202511\PhpParser\Node\Stmt\ClassMethod;
use Rector\Configuration\Parameter\FeatureFlags;
use Rector\DeadCode\NodeManipulator\ClassMethodParamRemover;
use Rector\NodeAnalyzer\MagicClassMethodAnalyzer;
use Rector\Php80\NodeAnalyzer\PhpAttributeAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\MethodName;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUnusedPublicMethodParameterRector\RemoveUnusedPublicMethodParameterRectorTest
 */
final class RemoveUnusedPublicMethodParameterRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\DeadCode\NodeManipulator\ClassMethodParamRemover
     */
    private $classMethodParamRemover;
    /**
     * @readonly
     * @var \Rector\NodeAnalyzer\MagicClassMethodAnalyzer
     */
    private $magicClassMethodAnalyzer;
    /**
     * @readonly
     * @var \Rector\Php80\NodeAnalyzer\PhpAttributeAnalyzer
     */
    private $phpAttributeAnalyzer;
    public function __construct(ClassMethodParamRemover $classMethodParamRemover, MagicClassMethodAnalyzer $magicClassMethodAnalyzer, PhpAttributeAnalyzer $phpAttributeAnalyzer)
    {
        $this->classMethodParamRemover = $classMethodParamRemover;
        $this->magicClassMethodAnalyzer = $magicClassMethodAnalyzer;
        $this->phpAttributeAnalyzer = $phpAttributeAnalyzer;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Remove unused parameter in public method on final class without extends and interface', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    public function run($a, $b)
    {
        echo $a;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    public function run($a)
    {
        echo $a;
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
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        // may have child, or override parent that needs to follow the signature
        if (!$node->isFinal() && FeatureFlags::treatClassesAsFinal($node) === \false) {
            return null;
        }
        if ($node->extends instanceof FullyQualified || $node->implements !== []) {
            return null;
        }
        $hasChanged = \false;
        foreach ($node->getMethods() as $classMethod) {
            if ($this->shouldSkipClassMethod($classMethod, $node)) {
                continue;
            }
            $changedMethod = $this->classMethodParamRemover->processRemoveParams($classMethod);
            if (!$changedMethod instanceof ClassMethod) {
                continue;
            }
            $hasChanged = \true;
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    private function shouldSkipClassMethod(ClassMethod $classMethod, Class_ $class): bool
    {
        // private method is handled by different rule
        if (!$classMethod->isPublic()) {
            return \true;
        }
        if ($classMethod->params === []) {
            return \true;
        }
        // parameter is required for contract coupling
        if ($this->isName($classMethod->name, MethodName::INVOKE) && $this->phpAttributeAnalyzer->hasPhpAttribute($class, 'Argtyper202511\Symfony\Component\Messenger\Attribute\AsMessageHandler')) {
            return \true;
        }
        return $this->magicClassMethodAnalyzer->isUnsafeOverridden($classMethod);
    }
}
