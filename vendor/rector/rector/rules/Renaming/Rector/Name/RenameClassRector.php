<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Renaming\Rector\Name;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr\ClassConstFetch;
use Argtyper202511\PhpParser\Node\FunctionLike;
use Argtyper202511\PhpParser\Node\Identifier;
use Argtyper202511\PhpParser\Node\Name;
use Argtyper202511\PhpParser\Node\Name\FullyQualified;
use Argtyper202511\PhpParser\Node\Stmt\ClassLike;
use Argtyper202511\PhpParser\Node\Stmt\Expression;
use Argtyper202511\PhpParser\Node\Stmt\If_;
use Argtyper202511\PhpParser\Node\Stmt\Property;
use Argtyper202511\PhpParser\NodeVisitor;
use Argtyper202511\PHPStan\Reflection\ReflectionProvider;
use Argtyper202511\Rector\Configuration\RenamedClassesDataCollector;
use Argtyper202511\Rector\Contract\Rector\ConfigurableRectorInterface;
use Argtyper202511\Rector\NodeTypeResolver\Node\AttributeKey;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Rector\Renaming\NodeManipulator\ClassRenamer;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Argtyper202511\RectorPrefix202511\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\Renaming\Rector\Name\RenameClassRector\RenameClassRectorTest
 */
final class RenameClassRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @readonly
     * @var \Rector\Configuration\RenamedClassesDataCollector
     */
    private $renamedClassesDataCollector;
    /**
     * @readonly
     * @var \Rector\Renaming\NodeManipulator\ClassRenamer
     */
    private $classRenamer;
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    public function __construct(RenamedClassesDataCollector $renamedClassesDataCollector, ClassRenamer $classRenamer, ReflectionProvider $reflectionProvider)
    {
        $this->renamedClassesDataCollector = $renamedClassesDataCollector;
        $this->classRenamer = $classRenamer;
        $this->reflectionProvider = $reflectionProvider;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Replace defined classes by new ones', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
namespace App;

use SomeOldClass;

function someFunction(SomeOldClass $someOldClass): SomeOldClass
{
    if ($someOldClass instanceof SomeOldClass) {
        return new SomeOldClass;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
namespace App;

use SomeNewClass;

function someFunction(SomeNewClass $someOldClass): SomeNewClass
{
    if ($someOldClass instanceof SomeNewClass) {
        return new SomeNewClass;
    }
}
CODE_SAMPLE
, ['Argtyper202511\\App\\SomeOldClass' => 'Argtyper202511\\App\\SomeNewClass'])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [
            ClassConstFetch::class,
            // place FullyQualified before Name on purpose executed early before the Name as parent
            FullyQualified::class,
            // Name as parent of FullyQualified executed later for fallback annotation to attribute rename to Name
            Name::class,
            Property::class,
            FunctionLike::class,
            Expression::class,
            ClassLike::class,
            If_::class,
        ];
    }
    /**
     * @param ClassConstFetch|FunctionLike|FullyQualified|Name|ClassLike|Expression|Property|If_ $node
     * @return null|NodeVisitor::DONT_TRAVERSE_CHILDREN|Node
     */
    public function refactor(Node $node)
    {
        $oldToNewClasses = $this->renamedClassesDataCollector->getOldToNewClasses();
        if ($oldToNewClasses === []) {
            return null;
        }
        if ($node instanceof ClassConstFetch) {
            return $this->processClassConstFetch($node, $oldToNewClasses);
        }
        $scope = $node->getAttribute(AttributeKey::SCOPE);
        return $this->classRenamer->renameNode($node, $oldToNewClasses, $scope);
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration) : void
    {
        Assert::allString($configuration);
        Assert::allString(\array_keys($configuration));
        $this->renamedClassesDataCollector->addOldToNewClasses($configuration);
    }
    /**
     * @param array<string, string> $oldToNewClasses
     * @return null|NodeVisitor::DONT_TRAVERSE_CHILDREN
     */
    private function processClassConstFetch(ClassConstFetch $classConstFetch, array $oldToNewClasses) : ?int
    {
        if (!$classConstFetch->class instanceof FullyQualified || !$classConstFetch->name instanceof Identifier || !$this->reflectionProvider->hasClass($classConstFetch->class->toString())) {
            return null;
        }
        foreach ($oldToNewClasses as $oldClass => $newClass) {
            if (!$this->isName($classConstFetch->class, $oldClass)) {
                continue;
            }
            if (!$this->reflectionProvider->hasClass($newClass)) {
                continue;
            }
            $classReflection = $this->reflectionProvider->getClass($newClass);
            if (!$classReflection->isInterface()) {
                continue;
            }
            $oldClassReflection = $this->reflectionProvider->getClass($oldClass);
            if ($oldClassReflection->hasConstant($classConstFetch->name->toString()) && !$classReflection->hasConstant($classConstFetch->name->toString())) {
                // no constant found on new interface? skip node below ClassConstFetch on this rule
                return NodeVisitor::DONT_TRAVERSE_CHILDREN;
            }
        }
        // continue to next Name usage
        return null;
    }
}
