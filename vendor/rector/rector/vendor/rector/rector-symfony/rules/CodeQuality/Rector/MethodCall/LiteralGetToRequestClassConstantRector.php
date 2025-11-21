<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Symfony\CodeQuality\Rector\MethodCall;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr\MethodCall;
use Argtyper202511\PhpParser\Node\Expr\StaticCall;
use Argtyper202511\PHPStan\Reflection\ReflectionProvider;
use Argtyper202511\PHPStan\Type\ObjectType;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Rector\Symfony\Enum\SymfonyClass;
use Argtyper202511\Rector\Symfony\NodeAnalyzer\LiteralCallLikeConstFetchReplacer;
use Argtyper202511\Rector\Symfony\ValueObject\ConstantMap\SymfonyRequestConstantMap;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Symfony\Tests\CodeQuality\Rector\MethodCall\LiteralGetToRequestClassConstantRector\LiteralGetToRequestClassConstantRectorTest
 */
final class LiteralGetToRequestClassConstantRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Symfony\NodeAnalyzer\LiteralCallLikeConstFetchReplacer
     */
    private $literalCallLikeConstFetchReplacer;
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    public function __construct(LiteralCallLikeConstFetchReplacer $literalCallLikeConstFetchReplacer, ReflectionProvider $reflectionProvider)
    {
        $this->literalCallLikeConstFetchReplacer = $literalCallLikeConstFetchReplacer;
        $this->reflectionProvider = $reflectionProvider;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Replace "GET" string by Symfony Request object class constants', [new CodeSample(<<<'CODE_SAMPLE'
use Symfony\Component\Form\FormBuilderInterface;

final class SomeClass
{
    public function detail(FormBuilderInterface $formBuilder)
    {
        $formBuilder->setMethod('GET');
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Component\Form\FormBuilderInterface;

final class SomeClass
{
    public function detail(FormBuilderInterface $formBuilder)
    {
        $formBuilder->setMethod(\Symfony\Component\HttpFoundation\Request::GET);
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
        return [MethodCall::class, StaticCall::class];
    }
    /**
     * @param MethodCall|StaticCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node instanceof StaticCall) {
            return $this->refactorStaticCall($node);
        }
        // for client, the transitional dependency to browser-kit might be missing and cause fatal error on PHPStan reflection
        // in most cases that should be skipped, @changelog https://github.com/rectorphp/rector/issues/7135
        if ($this->reflectionProvider->hasClass(SymfonyClass::ABSTRACT_BROWSER) && $this->isObjectType($node->var, new ObjectType(SymfonyClass::HTTP_CLIENT)) | $this->isObjectType($node->var, new ObjectType(SymfonyClass::KERNEL_BROWSER))) {
            return $this->refactorClientMethodCall($node);
        }
        if (!$this->isName($node->name, 'setMethod')) {
            return null;
        }
        if (!$this->isObjectType($node->var, new ObjectType(SymfonyClass::FORM_BUILDER))) {
            return null;
        }
        return $this->literalCallLikeConstFetchReplacer->replaceArgOnPosition($node, 0, SymfonyClass::REQUEST, SymfonyRequestConstantMap::METHOD_TO_CONST);
    }
    private function refactorStaticCall(StaticCall $staticCall): ?\Argtyper202511\PhpParser\Node\Expr\StaticCall
    {
        if (!$this->isName($staticCall->name, 'create')) {
            return null;
        }
        if (!$this->isObjectType($staticCall->class, new ObjectType(SymfonyClass::REQUEST))) {
            return null;
        }
        return $this->literalCallLikeConstFetchReplacer->replaceArgOnPosition($staticCall, 1, SymfonyClass::REQUEST, SymfonyRequestConstantMap::METHOD_TO_CONST);
    }
    private function refactorClientMethodCall(MethodCall $methodCall): ?\Argtyper202511\PhpParser\Node\Expr\MethodCall
    {
        if (!$this->isName($methodCall->name, 'request')) {
            return null;
        }
        return $this->literalCallLikeConstFetchReplacer->replaceArgOnPosition($methodCall, 0, SymfonyClass::REQUEST, SymfonyRequestConstantMap::METHOD_TO_CONST);
    }
}
