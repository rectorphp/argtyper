<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\DowngradePhp80\Rector\StaticCall;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Arg;
use Argtyper202511\PhpParser\Node\Expr\ArrayDimFetch;
use Argtyper202511\PhpParser\Node\Expr\FuncCall;
use Argtyper202511\PhpParser\Node\Expr\MethodCall;
use Argtyper202511\PhpParser\Node\Expr\PropertyFetch;
use Argtyper202511\PhpParser\Node\Expr\StaticCall;
use Argtyper202511\PhpParser\Node\Expr\Ternary;
use Argtyper202511\PhpParser\Node\Name;
use Argtyper202511\PhpParser\Node\Scalar\Int_;
use Argtyper202511\PHPStan\Type\ObjectType;
use Argtyper202511\PHPStan\Type\ThisType;
use Argtyper202511\PHPStan\Type\Type;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DowngradePhp80\Rector\StaticCall\DowngradePhpTokenRector\DowngradePhpTokenRectorTest
 */
final class DowngradePhpTokenRector extends AbstractRector
{
    /**
     * @var string
     */
    private const PHP_TOKEN = 'PhpToken';
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('"something()" will be renamed to "somethingElse()"', [new CodeSample(<<<'CODE_SAMPLE'
$tokens = \PhpToken::tokenize($code);

foreach ($tokens as $phpToken) {
   $name = $phpToken->getTokenName();
   $text = $phpToken->text;
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$tokens = token_get_all($code);

foreach ($tokens as $token) {
    $name = is_array($token) ? token_name($token[0]) : null;
    $text = is_array($token) ? $token[1] : $token;
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [StaticCall::class, MethodCall::class, PropertyFetch::class];
    }
    /**
     * @param StaticCall|MethodCall|PropertyFetch $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($node instanceof StaticCall) {
            return $this->refactorStaticCall($node);
        }
        if ($node instanceof MethodCall) {
            return $this->refactorMethodCall($node);
        }
        return $this->refactorPropertyFetch($node);
    }
    private function refactorStaticCall(StaticCall $staticCall) : ?FuncCall
    {
        if (!$this->isName($staticCall->name, 'tokenize')) {
            return null;
        }
        if (!$this->isObjectType($staticCall->class, new ObjectType(self::PHP_TOKEN))) {
            return null;
        }
        if ($this->skipPhpParserInternalToken($this->getType($staticCall->class))) {
            return null;
        }
        return new FuncCall(new Name('token_get_all'), $staticCall->args);
    }
    private function refactorMethodCall(MethodCall $methodCall) : ?Ternary
    {
        if (!$this->isName($methodCall->name, 'getTokenName')) {
            return null;
        }
        if (!$this->isObjectType($methodCall->var, new ObjectType(self::PHP_TOKEN))) {
            return null;
        }
        if ($this->skipPhpParserInternalToken($this->getType($methodCall->var))) {
            return null;
        }
        $isArrayFuncCall = new FuncCall(new Name('is_array'), [new Arg($methodCall->var)]);
        $arrayDimFetch = new ArrayDimFetch($methodCall->var, new Int_(0));
        $tokenGetNameFuncCall = new FuncCall(new Name('token_name'), [new Arg($arrayDimFetch)]);
        return new Ternary($isArrayFuncCall, $tokenGetNameFuncCall, $this->nodeFactory->createNull());
    }
    private function refactorPropertyFetch(PropertyFetch $propertyFetch) : ?Ternary
    {
        $propertyFetchName = $this->getName($propertyFetch->name);
        if (!\in_array($propertyFetchName, ['id', 'text'], \true)) {
            return null;
        }
        if (!$this->isObjectType($propertyFetch->var, new ObjectType(self::PHP_TOKEN))) {
            return null;
        }
        if ($this->skipPhpParserInternalToken($this->getType($propertyFetch->var))) {
            return null;
        }
        $isArrayFuncCall = new FuncCall(new Name('is_array'), [new Arg($propertyFetch->var)]);
        $arrayDimFetch = new ArrayDimFetch($propertyFetch->var, $propertyFetchName === 'id' ? new Int_(0) : new Int_(1));
        return new Ternary($isArrayFuncCall, $arrayDimFetch, $propertyFetch->var);
    }
    private function skipPhpParserInternalToken(Type $type) : bool
    {
        if ($type instanceof ThisType) {
            $type = $type->getStaticObjectType();
        }
        if ($type instanceof ObjectType) {
            return $type->isInstanceOf('Argtyper202511\\PhpParser\\Internal\\TokenPolyfill')->yes();
        }
        return \false;
    }
}
