<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Symfony\DependencyInjection\Rector\Class_;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr\MethodCall;
use Argtyper202511\PhpParser\Node\Stmt\Class_;
use Argtyper202511\PHPStan\Reflection\ClassReflection;
use Argtyper202511\Rector\Naming\Naming\PropertyNaming;
use Argtyper202511\Rector\NodeManipulator\ClassDependencyManipulator;
use Argtyper202511\Rector\PHPStan\ScopeFetcher;
use Argtyper202511\Rector\PostRector\ValueObject\PropertyMetadata;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
use Argtyper202511\Rector\Symfony\DependencyInjection\NodeDecorator\CommandConstructorDecorator;
use Argtyper202511\Rector\Symfony\DependencyInjection\ThisGetTypeMatcher;
use Argtyper202511\Rector\Symfony\Enum\SymfonyClass;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Symfony\Tests\DependencyInjection\Rector\Class_\CommandGetByTypeToConstructorInjectionRector\CommandGetByTypeToConstructorInjectionRectorTest
 */
final class CommandGetByTypeToConstructorInjectionRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\NodeManipulator\ClassDependencyManipulator
     */
    private $classDependencyManipulator;
    /**
     * @readonly
     * @var \Rector\Naming\Naming\PropertyNaming
     */
    private $propertyNaming;
    /**
     * @readonly
     * @var \Rector\Symfony\DependencyInjection\NodeDecorator\CommandConstructorDecorator
     */
    private $commandConstructorDecorator;
    /**
     * @readonly
     * @var \Rector\Symfony\DependencyInjection\ThisGetTypeMatcher
     */
    private $thisGetTypeMatcher;
    public function __construct(ClassDependencyManipulator $classDependencyManipulator, PropertyNaming $propertyNaming, CommandConstructorDecorator $commandConstructorDecorator, ThisGetTypeMatcher $thisGetTypeMatcher)
    {
        $this->classDependencyManipulator = $classDependencyManipulator;
        $this->propertyNaming = $propertyNaming;
        $this->commandConstructorDecorator = $commandConstructorDecorator;
        $this->thisGetTypeMatcher = $thisGetTypeMatcher;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('From `$container->get(SomeType::class)` in commands to constructor injection (step 2/x)', [new CodeSample(<<<'CODE_SAMPLE'
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

final class SomeCommand extends ContainerAwareCommand
{
    public function someMethod()
    {
        $someType = $this->get(SomeType::class);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

final class SomeCommand extends ContainerAwareCommand
{
    public function __construct(private SomeType $someType)
    {
    }

    public function someMethod()
    {
        $someType = $this->someType;
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($this->shouldSkipClass($node)) {
            return null;
        }
        $propertyMetadatas = [];
        $this->traverseNodesWithCallable($node, function (Node $node) use(&$propertyMetadatas) : ?Node {
            if (!$node instanceof MethodCall) {
                return null;
            }
            $className = $this->thisGetTypeMatcher->match($node);
            if (!\is_string($className)) {
                return null;
            }
            $propertyName = $this->propertyNaming->fqnToVariableName($className);
            $propertyMetadata = new PropertyMetadata($propertyName, new FullyQualifiedObjectType($className));
            $propertyMetadatas[] = $propertyMetadata;
            return $this->nodeFactory->createPropertyFetch('this', $propertyMetadata->getName());
        });
        if ($propertyMetadatas === []) {
            return null;
        }
        foreach ($propertyMetadatas as $propertyMetadata) {
            $this->classDependencyManipulator->addConstructorDependency($node, $propertyMetadata);
        }
        $this->commandConstructorDecorator->decorate($node);
        return $node;
    }
    private function shouldSkipClass(Class_ $class) : bool
    {
        // keep it safe
        if ($class->isAbstract()) {
            return \true;
        }
        $scope = ScopeFetcher::fetch($class);
        $classReflection = $scope->getClassReflection();
        if (!$classReflection instanceof ClassReflection) {
            return \true;
        }
        return !$classReflection->is(SymfonyClass::CONTAINER_AWARE_COMMAND);
    }
}
