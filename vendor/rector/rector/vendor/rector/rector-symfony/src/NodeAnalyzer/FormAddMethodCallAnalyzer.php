<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Symfony\NodeAnalyzer;

use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Expr\MethodCall;
use Argtyper202511\PHPStan\Type\ObjectType;
use Argtyper202511\Rector\NodeNameResolver\NodeNameResolver;
use Argtyper202511\Rector\NodeTypeResolver\NodeTypeResolver;
final class FormAddMethodCallAnalyzer
{
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @var ObjectType[]
     */
    private $formObjectTypes = [];
    public function __construct(NodeTypeResolver $nodeTypeResolver, NodeNameResolver $nodeNameResolver)
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->formObjectTypes = [new ObjectType('Argtyper202511\Symfony\Component\Form\FormBuilderInterface'), new ObjectType('Argtyper202511\Symfony\Component\Form\FormInterface')];
    }
    public function isMatching(MethodCall $methodCall): bool
    {
        if (!$this->nodeNameResolver->isName($methodCall->name, 'add')) {
            return \false;
        }
        if (!$this->nodeTypeResolver->isObjectTypes($methodCall->var, $this->formObjectTypes)) {
            return \false;
        }
        // just one argument
        $args = $methodCall->getArgs();
        if (!isset($args[1])) {
            return \false;
        }
        $firstArg = $args[1];
        return $firstArg->value instanceof Expr;
    }
}
