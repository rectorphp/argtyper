<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Doctrine\DoctrineFixture\Rector\MethodCall;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Arg;
use Argtyper202511\PhpParser\Node\Expr\ClassConstFetch;
use Argtyper202511\PhpParser\Node\Expr\MethodCall;
use Argtyper202511\PhpParser\Node\Name\FullyQualified;
use Argtyper202511\PHPStan\Reflection\ClassReflection;
use Argtyper202511\PHPStan\Type\ObjectType;
use Argtyper202511\Rector\Doctrine\DoctrineFixture\Reflection\ParameterTypeResolver;
use Argtyper202511\Rector\Doctrine\Enum\DoctrineClass;
use Argtyper202511\Rector\PHPStan\ScopeFetcher;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Doctrine\Tests\DoctrineFixture\Rector\MethodCall\AddGetReferenceTypeRector\AddGetReferenceTypeRectorTest
 *
 * @see https://github.com/doctrine/data-fixtures/pull/409/files
 */
final class AddGetReferenceTypeRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Doctrine\DoctrineFixture\Reflection\ParameterTypeResolver
     */
    private $parameterTypeResolver;
    public function __construct(ParameterTypeResolver $parameterTypeResolver)
    {
        $this->parameterTypeResolver = $parameterTypeResolver;
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [MethodCall::class];
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change $this->getReference() in data fixtures to fill reference class directly', [new CodeSample(<<<'CODE_SAMPLE'
use Doctrine\Common\DataFixtures\AbstractDataFixture;

final class SomeFixture extends AbstractDataFixture
{
    public function run(SomeEntity $someEntity)
    {
        $someEntity->setSomePassedEntity($this->getReference('some-key'));
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Doctrine\Common\DataFixtures\AbstractDataFixture;

final class SomeFixture extends AbstractDataFixture
{
    public function run(SomeEntity $someEntity)
    {
        $someEntity->setSomePassedEntity($this->getReference('some-key'), SomeReference::class);
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?\Argtyper202511\PhpParser\Node\Expr\MethodCall
    {
        if (!$this->isInAbstractFixture($node)) {
            return null;
        }
        if ($node->isFirstClassCallable()) {
            return null;
        }
        if (count($node->getArgs()) !== 1) {
            return null;
        }
        $soleArg = $node->getArgs()[0];
        if (!$soleArg->value instanceof MethodCall) {
            return null;
        }
        $nestedMethodCall = $soleArg->value;
        if (!$this->isName($nestedMethodCall->name, 'getReference')) {
            return null;
        }
        // already filled type
        if (count($nestedMethodCall->getArgs()) === 2) {
            return null;
        }
        $callerParameterObjetType = $this->parameterTypeResolver->resolveCallerFirstParameterObjectType($node);
        if (!$callerParameterObjetType instanceof ObjectType) {
            return null;
        }
        $nestedMethodCall->args[] = new Arg(new ClassConstFetch(new FullyQualified($callerParameterObjetType->getClassName()), 'class'));
        return $node;
    }
    private function isInAbstractFixture(MethodCall $methodCall): bool
    {
        $scope = ScopeFetcher::fetch($methodCall);
        $classReflection = $scope->getClassReflection();
        if (!$classReflection instanceof ClassReflection) {
            return \false;
        }
        return $classReflection->is(DoctrineClass::ABSTRACT_FIXTURE);
    }
}
