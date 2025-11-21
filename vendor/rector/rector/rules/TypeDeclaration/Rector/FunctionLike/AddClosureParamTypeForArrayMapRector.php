<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\TypeDeclaration\Rector\FunctionLike;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Arg;
use Argtyper202511\PhpParser\Node\Expr\Closure;
use Argtyper202511\PhpParser\Node\Expr\FuncCall;
use Argtyper202511\PhpParser\Node\Param;
use Argtyper202511\PhpParser\Node\VariadicPlaceholder;
use Argtyper202511\PHPStan\Reflection\Native\NativeFunctionReflection;
use Argtyper202511\PHPStan\Type\ArrayType;
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
 * @see \Rector\Tests\TypeDeclaration\Rector\FunctionLike\AddClosureParamTypeForArrayMapRector\AddClosureParamTypeForArrayMapRectorTest
 */
final class AddClosureParamTypeForArrayMapRector extends AbstractRector
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
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Applies type hints to array_map closures', [new CodeSample(<<<'CODE_SAMPLE'
array_map(function ($value, $key): string {
    return $value . $key;
}, $strings);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
array_map(function (string $value, int $key): bool {
    return $value . $key;
}, $strings);
CODE_SAMPLE
)]);
    }
    public function getNodeTypes(): array
    {
        return [FuncCall::class];
    }
    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node->isFirstClassCallable()) {
            return null;
        }
        if (!$this->isName($node, 'array_map')) {
            return null;
        }
        $funcReflection = $this->reflectionResolver->resolveFunctionLikeReflectionFromCall($node);
        if (!$funcReflection instanceof NativeFunctionReflection) {
            return null;
        }
        $args = $node->getArgs();
        if (!isset($args[0]) || !$args[0]->value instanceof Closure) {
            return null;
        }
        /** @var ArrayType[] $types */
        $types = array_filter(array_map(function ($arg): ?ArrayType {
            if (!$arg instanceof Arg) {
                return null;
            }
            $type = $this->getType($arg->value);
            if ($type instanceof ArrayType) {
                return $type;
            }
            return null;
        }, array_slice($node->args, 1)));
        $values = [];
        $keys = [];
        foreach ($types as $type) {
            $values[] = $type->getIterableValueType();
            $keys[] = $type->getIterableKeyType();
        }
        foreach ($values as $value) {
            if ($value instanceof MixedType) {
                $values = [];
                break;
            } elseif ($value instanceof UnionType) {
                $values = array_merge($values, $value->getTypes());
            }
        }
        foreach ($keys as $key) {
            if ($key instanceof MixedType) {
                $keys = [];
                break;
            } elseif ($key instanceof UnionType) {
                $keys = array_merge($keys, $key->getTypes());
            }
        }
        $filter = function (Type $type): bool {
            return !$type instanceof UnionType;
        };
        $valueType = $this->combineTypes(array_filter($values, $filter));
        $keyType = $this->combineTypes(array_filter($keys, $filter));
        if (!$keyType instanceof Type && !$valueType instanceof Type) {
            return null;
        }
        if ($this->updateClosureWithTypes($args[0]->value, $keyType, $valueType)) {
            return $node;
        }
        return null;
    }
    private function updateClosureWithTypes(Closure $closure, ?Type $keyType, ?Type $valueType): bool
    {
        $changes = \false;
        $valueParam = $closure->params[0] ?? null;
        $keyParam = $closure->params[1] ?? null;
        if ($valueParam instanceof Param && $valueType instanceof Type && $this->refactorParameter($closure->params[0], $valueType)) {
            $changes = \true;
        }
        if ($keyParam instanceof Param && $keyType instanceof Type && $this->refactorParameter($closure->params[1], $keyType)) {
            return \true;
        }
        return $changes;
    }
    private function refactorParameter(Param $param, Type $type): bool
    {
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
    private function combineTypes(array $types): ?Type
    {
        if ($types === []) {
            return null;
        }
        $types = array_reduce($types, function (array $types, Type $type): array {
            foreach ($types as $previousType) {
                if ($this->typeComparator->areTypesEqual($type, $previousType)) {
                    return $types;
                }
            }
            $types[] = $type;
            return $types;
        }, []);
        if (count($types) === 1) {
            return $types[0];
        }
        return new UnionType(UnionTypeHelper::sortTypes($types));
    }
}
