<?php

declare (strict_types=1);
namespace Rector\Symfony\Symfony63\Rector\Class_;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Stmt\Class_;
use Argtyper202511\PhpParser\Node\Stmt\ClassMethod;
use Argtyper202511\PhpParser\Node\Stmt\Return_;
use Argtyper202511\PHPStan\Type\Constant\ConstantBooleanType;
use Argtyper202511\PHPStan\Type\IntegerType;
use Argtyper202511\PHPStan\Type\UnionType;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\Rector\AbstractRector;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Rector\Symfony\NodeAnalyzer\ClassAnalyzer;
use Rector\VendorLocker\ParentClassMethodTypeOverrideGuard;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Symfony\Tests\Symfony63\Rector\Class_\SignalableCommandInterfaceReturnTypeRector\SignalableCommandInterfaceReturnTypeRectorTest
 */
final class SignalableCommandInterfaceReturnTypeRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Symfony\NodeAnalyzer\ClassAnalyzer
     */
    private $classAnalyzer;
    /**
     * @readonly
     * @var \Rector\VendorLocker\ParentClassMethodTypeOverrideGuard
     */
    private $parentClassMethodTypeOverrideGuard;
    /**
     * @readonly
     * @var \Rector\StaticTypeMapper\StaticTypeMapper
     */
    private $staticTypeMapper;
    public function __construct(ClassAnalyzer $classAnalyzer, ParentClassMethodTypeOverrideGuard $parentClassMethodTypeOverrideGuard, StaticTypeMapper $staticTypeMapper)
    {
        $this->classAnalyzer = $classAnalyzer;
        $this->parentClassMethodTypeOverrideGuard = $parentClassMethodTypeOverrideGuard;
        $this->staticTypeMapper = $staticTypeMapper;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Return int or false from SignalableCommandInterface::handleSignal() instead of void', [new CodeSample(<<<'CODE_SAMPLE'
    public function handleSignal(int $signal): void
    {
    }
CODE_SAMPLE
, <<<'CODE_SAMPLE'

    public function handleSignal(int $signal): int|false
    {
        return false;
    }
CODE_SAMPLE
)]);
    }
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if (!$this->classAnalyzer->hasImplements($node, 'Argtyper202511\Symfony\Component\Console\Command\SignalableCommandInterface')) {
            return null;
        }
        $classMethod = $node->getMethod('handleSignal');
        if (!$classMethod instanceof ClassMethod) {
            return null;
        }
        $unionType = new UnionType([new IntegerType(), new ConstantBooleanType(\false)]);
        if ($this->parentClassMethodTypeOverrideGuard->shouldSkipReturnTypeChange($classMethod, $unionType)) {
            return null;
        }
        $classMethod->returnType = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($unionType, TypeKind::RETURN);
        $classMethod->stmts[] = new Return_($this->nodeFactory->createFalse());
        return $node;
    }
}
