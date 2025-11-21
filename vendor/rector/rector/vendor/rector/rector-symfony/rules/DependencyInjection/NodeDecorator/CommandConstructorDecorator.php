<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Symfony\DependencyInjection\NodeDecorator;

use Argtyper202511\PhpParser\Node\Expr\StaticCall;
use Argtyper202511\PhpParser\Node\Name;
use Argtyper202511\PhpParser\Node\Stmt\Class_;
use Argtyper202511\PhpParser\Node\Stmt\ClassMethod;
use Argtyper202511\PhpParser\Node\Stmt\Expression;
use Argtyper202511\PHPStan\Type\ObjectType;
use Argtyper202511\Rector\NodeTypeResolver\NodeTypeResolver;
use Argtyper202511\Rector\ValueObject\MethodName;
final class CommandConstructorDecorator
{
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    public function __construct(NodeTypeResolver $nodeTypeResolver)
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
    }
    public function decorate(Class_ $class): void
    {
        // special case for command to keep parent constructor call
        if (!$this->nodeTypeResolver->isObjectType($class, new ObjectType('Argtyper202511\Symfony\Component\Console\Command\Command'))) {
            return;
        }
        $constructClassMethod = $class->getMethod(MethodName::CONSTRUCT);
        if (!$constructClassMethod instanceof ClassMethod) {
            return;
        }
        // empty stmts? add parent::__construct() to setup command
        if ((array) $constructClassMethod->stmts === []) {
            $parentConstructStaticCall = new StaticCall(new Name('parent'), '__construct');
            $constructClassMethod->stmts[] = new Expression($parentConstructStaticCall);
        }
    }
}
