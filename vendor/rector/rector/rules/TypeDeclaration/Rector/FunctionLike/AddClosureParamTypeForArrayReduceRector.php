<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\TypeDeclaration\Rector\FunctionLike;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr\Closure;
use Argtyper202511\PhpParser\Node\Expr\FuncCall;
use Argtyper202511\PhpParser\Node\Param;
use Argtyper202511\PHPStan\Reflection\Native\NativeFunctionReflection;
use Argtyper202511\PHPStan\Type\ClosureType;
use Argtyper202511\PHPStan\Type\IntersectionType;
use Argtyper202511\PHPStan\Type\MixedType;
use Argtyper202511\PHPStan\Type\Type;
use Argtyper202511\PHPStan\Type\UnionType;
use Argtyper202511\PHPStan\Type\UnionTypeHelper;
use Argtyper202511\Rector\NodeTypeResolver\TypeComparator\TypeComparator;
use Argtyper202511\Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Rector\Reflection\ReflectionResolver;
use Argtyper202511\Rector\StaticTypeMapper\StaticTypeMapper;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\FunctionLike\AddClosureParamTypeForArrayReduceRector\AddClosureParamTypeForArrayReduceRectorTest
 */
final class AddClosureParamTypeForArrayReduceRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\TypeComparator\TypeComparator
     */
    private $typeComparator;
    /**
     * @readonly
     * @var \Rector\StaticTypeMapper\StaticTypeMapper
     */
    private $staticTypeMapper;
    /**
     * @readonly
     * @var \Rector\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    public function __construct(TypeComparator $typeComparator, StaticTypeMapper $staticTypeMapper, ReflectionResolver $reflectionResolver)
    {
        $this->typeComparator = $typeComparator;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->reflectionResolver = $reflectionResolver;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Applies type hints to array_map closures', [new CodeSample(<<<'CODE_SAMPLE'
array_reduce($strings, function ($carry, $value, $key): string {
    return $carry . $value;
}, $initialString);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
array_reduce($strings, function (string $carry, string $value): string {
    return $carry . $value;
}, $initialString);
CODE_SAMPLE
)]);
    }
    public function getNodeTypes() : array
    {
        return [FuncCall::class];
    }
    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($node->isFirstClassCallable()) {
            return null;
        }
        if (!$this->isName($node, 'array_reduce')) {
            return null;
        }
        $funcReflection = $this->reflectionResolver->resolveFunctionLikeReflectionFromCall($node);
        if (!$funcReflection instanceof NativeFunctionReflection) {
            return null;
        }
        $args = $node->getArgs();
        if (!isset($args[1]) || !$args[1]->value instanceof Closure) {
            return null;
        }
        $closureType = $this->getType($args[1]->value);
        if (!$closureType instanceof ClosureType) {
            return null;
        }
        $carryType = $closureType->getReturnType();
        if (isset($args[2])) {
            $carryType = $this->combineTypes([$this->getType($args[2]->value), $carryType]);
        }
        $type = $this->getType($args[0]->value);
        $valueType = $type->getIterableValueType();
        if ($this->updateClosureWithTypes($args[1]->value, $valueType, $carryType)) {
            return $node;
        }
        return null;
    }
    private function updateClosureWithTypes(Closure $closure, ?Type $valueType, ?Type $carryType) : bool
    {
        $changes = \false;
        $carryParam = $closure->params[0] ?? null;
        $valueParam = $closure->params[1] ?? null;
        if ($valueParam instanceof Param && $valueType instanceof Type && $this->refactorParameter($valueParam, $valueType)) {
            $changes = \true;
        }
        if ($carryParam instanceof Param && $carryType instanceof Type && $this->refactorParameter($carryParam, $carryType)) {
            return \true;
        }
        return $changes;
    }
    private function refactorParameter(Param $param, Type $type) : bool
    {
        if ($type instanceof MixedType) {
            return \false;
        }
        // already set → no change
        if ($param->type instanceof Node) {
            return \false;
        }
        $paramTypeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($type, TypeKind::PARAM);
        if (!$paramTypeNode instanceof Node) {
            return \false;
        }
        $param->type = $paramTypeNode;
        return \true;
    }
    /**
     * @param Type[] $types
     */
    private function combineTypes(array $types) : ?Type
    {
        if ($types === []) {
            return null;
        }
        $types = \array_reduce($types, function (array $types, Type $type) : array {
            foreach ($types as $previousType) {
                if ($this->typeComparator->areTypesEqual($type, $previousType)) {
                    return $types;
                }
            }
            $types[] = $type;
            return $types;
        }, []);
        if (\count($types) === 1) {
            return $types[0];
        }
        foreach ($types as $type) {
            if ($type instanceof UnionType) {
                foreach ($type->getTypes() as $unionedType) {
                    if ($unionedType instanceof IntersectionType) {
                        return null;
                    }
                }
            }
        }
        return new UnionType(UnionTypeHelper::sortTypes($types));
    }
}
