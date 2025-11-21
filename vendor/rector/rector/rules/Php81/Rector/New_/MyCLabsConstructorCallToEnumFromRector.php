<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Php81\Rector\New_;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr\New_;
use Argtyper202511\PhpParser\Node\Expr\StaticCall;
use Argtyper202511\PhpParser\Node\Name\FullyQualified;
use Argtyper202511\PHPStan\Reflection\ReflectionProvider;
use Argtyper202511\PHPStan\Type\ObjectType;
use Argtyper202511\Rector\Enum\ObjectReference;
use Argtyper202511\Rector\PHPStan\ScopeFetcher;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Rector\ValueObject\MethodName;
use Argtyper202511\Rector\ValueObject\PhpVersionFeature;
use Argtyper202511\Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Php81\Rector\New_\MyCLabsConstructorCallToEnumFromRector\MyCLabsConstructorCallToEnumFromRectorTest
 */
final class MyCLabsConstructorCallToEnumFromRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    private const MY_C_LABS_CLASS = 'Argtyper202511\MyCLabs\Enum\Enum';
    private const DEFAULT_ENUM_CONSTRUCTOR = 'from';
    public function __construct(ReflectionProvider $reflectionProvider)
    {
        $this->reflectionProvider = $reflectionProvider;
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [New_::class];
    }
    /**
     * @param New_ $node
     */
    public function refactor(Node $node): ?Node
    {
        return $this->refactorConstructorCallToStaticFromCall($node);
    }
    public function provideMinPhpVersion(): int
    {
        return PhpVersionFeature::ENUM;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Refactor MyCLabs Enum using constructor for instantiation', [new CodeSample(<<<'CODE_SAMPLE'
$enum = new Enum($args);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$enum = Enum::from($args);
CODE_SAMPLE
)]);
    }
    private function refactorConstructorCallToStaticFromCall(New_ $new): ?StaticCall
    {
        if (!$this->isObjectType($new->class, new ObjectType(self::MY_C_LABS_CLASS))) {
            return null;
        }
        $classname = $this->getName($new->class);
        if (in_array($classname, [ObjectReference::SELF, ObjectReference::STATIC], \true)) {
            $classname = ($nullsafeVariable1 = ScopeFetcher::fetch($new)->getClassReflection()) ? $nullsafeVariable1->getName() : null;
        }
        if ($classname === null) {
            return null;
        }
        if (!$this->isMyCLabsConstructor($new, $classname)) {
            return null;
        }
        return new StaticCall(new FullyQualified($classname), self::DEFAULT_ENUM_CONSTRUCTOR, $new->args);
    }
    private function isMyCLabsConstructor(New_ $new, string $classname): bool
    {
        $classReflection = $this->reflectionProvider->getClass($classname);
        if (!$classReflection->hasMethod(MethodName::CONSTRUCT)) {
            return \true;
        }
        return $classReflection->getMethod(MethodName::CONSTRUCT, ScopeFetcher::fetch($new))->getDeclaringClass()->getName() === self::MY_C_LABS_CLASS;
    }
}
