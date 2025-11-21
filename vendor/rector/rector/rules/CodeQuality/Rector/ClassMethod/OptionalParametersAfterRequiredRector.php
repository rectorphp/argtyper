<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\CodeQuality\Rector\ClassMethod;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\ComplexType;
use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Expr\Closure;
use Argtyper202511\PhpParser\Node\Expr\ConstFetch;
use Argtyper202511\PhpParser\Node\Identifier;
use Argtyper202511\PhpParser\Node\IntersectionType;
use Argtyper202511\PhpParser\Node\Name;
use Argtyper202511\PhpParser\Node\NullableType;
use Argtyper202511\PhpParser\Node\Param;
use Argtyper202511\PhpParser\Node\Scalar\Float_;
use Argtyper202511\PhpParser\Node\Scalar\Int_;
use Argtyper202511\PhpParser\Node\Scalar\String_;
use Argtyper202511\PhpParser\Node\Stmt\ClassMethod;
use Argtyper202511\PhpParser\Node\Stmt\Function_;
use Argtyper202511\PhpParser\Node\UnionType;
use Argtyper202511\Rector\PhpParser\Node\Value\ValueResolver;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Rector\ValueObject\PhpVersionFeature;
use Argtyper202511\Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\ClassMethod\OptionalParametersAfterRequiredRector\OptionalParametersAfterRequiredRectorTest
 */
final class OptionalParametersAfterRequiredRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\Value\ValueResolver
     */
    private $valueResolver;
    public function __construct(ValueResolver $valueResolver)
    {
        $this->valueResolver = $valueResolver;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add reasonable default value when a required parameter follows an optional one', [new CodeSample(<<<'CODE_SAMPLE'
class SomeObject
{
    public function run($optional = 1, int $required)
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeObject
{
    public function run($optional = 1, int $required = 0)
    {
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
        return [ClassMethod::class, Function_::class, Closure::class];
    }
    /**
     * @param ClassMethod|Function_|Closure $node
     * @return \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_|\PhpParser\Node\Expr\Closure|null
     */
    public function refactor(Node $node)
    {
        if ($node->params === []) {
            return null;
        }
        $hasChanged = \false;
        foreach ($node->params as $key => $param) {
            if ($param->default instanceof Expr) {
                continue;
            }
            if ($param->variadic) {
                continue;
            }
            $previousParam = $node->params[$key - 1] ?? null;
            if ($previousParam instanceof Param && $previousParam->default instanceof Expr) {
                $hasChanged = \true;
                $this->processParam($param);
            }
        }
        return $hasChanged ? $node : null;
    }
    public function provideMinPhpVersion(): int
    {
        return PhpVersionFeature::NULLABLE_TYPE;
    }
    /**
     * Look first found type reasonable value
     *
     * @param Node[] $types
     */
    private function mapReasonableParamValue(array $types): Expr
    {
        foreach ($types as $type) {
            if ($this->isName($type, 'string')) {
                return new String_('');
            }
            if ($this->isName($type, 'int')) {
                return new Int_(0);
            }
            if ($this->isName($type, 'float')) {
                return new Float_(0.0);
            }
            if ($this->isName($type, 'bool')) {
                return $this->nodeFactory->createFalse();
            }
            if ($this->isName($type, 'array')) {
                return $this->nodeFactory->createArray([]);
            }
            if ($this->isName($type, 'true')) {
                return $this->nodeFactory->createTrue();
            }
            if ($this->isName($type, 'false')) {
                return $this->nodeFactory->createFalse();
            }
        }
        return new ConstFetch(new Name('null'));
    }
    private function processParam(Param $param): void
    {
        if (!$param->type instanceof Node) {
            $param->default = new ConstFetch(new Name('null'));
            return;
        }
        if ($param->type instanceof NullableType) {
            $param->default = new ConstFetch(new Name('null'));
            return;
        }
        if ($param->type instanceof IntersectionType) {
            $param->default = new ConstFetch(new Name('null'));
            $param->type = new UnionType([$param->type, new Identifier('null')]);
            return;
        }
        if ($param->type instanceof UnionType) {
            foreach ($param->type->types as $unionedType) {
                if ($unionedType instanceof Identifier && $this->isName($unionedType, 'null')) {
                    $param->default = new ConstFetch(new Name('null'));
                    return;
                }
            }
            $reasonableValue = $this->mapReasonableParamValue($param->type->types);
            if ($this->valueResolver->isNull($reasonableValue)) {
                $param->default = new ConstFetch(new Name('null'));
                $param->type->types[] = new Identifier('null');
                return;
            }
            $param->default = $reasonableValue;
            return;
        }
        if ($param->type instanceof ComplexType) {
            return;
        }
        $reasonableValue = $this->mapReasonableParamValue([$param->type]);
        if ($this->valueResolver->isNull($reasonableValue)) {
            if (!$param->type instanceof Identifier || !$this->isNames($param->type, ['null', 'mixed'])) {
                $param->type = new NullableType($param->type);
            }
            $param->default = new ConstFetch(new Name('null'));
            return;
        }
        $param->default = $reasonableValue;
    }
}
