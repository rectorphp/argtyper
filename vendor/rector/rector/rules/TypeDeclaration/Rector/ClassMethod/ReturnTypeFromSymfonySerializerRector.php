<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\ClassMethod;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr\MethodCall;
use Argtyper202511\PhpParser\Node\Name\FullyQualified;
use Argtyper202511\PhpParser\Node\Stmt\ClassMethod;
use Argtyper202511\PhpParser\Node\Stmt\Return_;
use Argtyper202511\PHPStan\Type\ObjectType;
use Rector\NodeAnalyzer\ArgsAnalyzer;
use Rector\PhpParser\Node\Value\ValueResolver;
use Rector\PHPStan\ScopeFetcher;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VendorLocker\NodeVendorLocker\ClassMethodReturnTypeOverrideGuard;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromSymfonySerializerRector\ReturnTypeFromSymfonySerializerRectorTest
 */
final class ReturnTypeFromSymfonySerializerRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \Rector\VendorLocker\NodeVendorLocker\ClassMethodReturnTypeOverrideGuard
     */
    private $classMethodReturnTypeOverrideGuard;
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\Value\ValueResolver
     */
    private $valueResolver;
    /**
     * @readonly
     * @var \Rector\NodeAnalyzer\ArgsAnalyzer
     */
    private $argsAnalyzer;
    public function __construct(ClassMethodReturnTypeOverrideGuard $classMethodReturnTypeOverrideGuard, ValueResolver $valueResolver, ArgsAnalyzer $argsAnalyzer)
    {
        $this->classMethodReturnTypeOverrideGuard = $classMethodReturnTypeOverrideGuard;
        $this->valueResolver = $valueResolver;
        $this->argsAnalyzer = $argsAnalyzer;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add return type from symfony serializer', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    private \Symfony\Component\Serializer\Serializer $serializer;

    public function resolveEntity($data)
    {
        return $this->serializer->deserialize($data, SomeType::class, 'json');
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    private \Symfony\Component\Serializer\Serializer $serializer;

    public function resolveEntity($data): SomeType
    {
        return $this->serializer->deserialize($data, SomeType::class, 'json');
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
        return [ClassMethod::class];
    }
    public function provideMinPhpVersion(): int
    {
        return PhpVersionFeature::HAS_RETURN_TYPE;
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node->stmts === null) {
            return null;
        }
        if ($node->returnType instanceof Node) {
            return null;
        }
        $scope = ScopeFetcher::fetch($node);
        if ($this->classMethodReturnTypeOverrideGuard->shouldSkipClassMethod($node, $scope)) {
            return null;
        }
        if (count($node->stmts) !== 1) {
            return null;
        }
        if (!$node->stmts[0] instanceof Return_ || !$node->stmts[0]->expr instanceof MethodCall) {
            return null;
        }
        /** @var MethodCall $returnExpr */
        $returnExpr = $node->stmts[0]->expr;
        if (!$this->isName($returnExpr->name, 'deserialize')) {
            return null;
        }
        if ($returnExpr->isFirstClassCallable()) {
            return null;
        }
        if (!$this->isObjectType($returnExpr->var, new ObjectType('Argtyper202511\Symfony\Component\Serializer\Serializer'))) {
            return null;
        }
        $args = $returnExpr->getArgs();
        if ($this->argsAnalyzer->hasNamedArg($args)) {
            return null;
        }
        if (count($args) !== 3) {
            return null;
        }
        $type = $this->valueResolver->getValue($args[1]->value);
        if (!is_string($type)) {
            return null;
        }
        $node->returnType = new FullyQualified($type);
        return $node;
    }
}
