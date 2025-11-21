<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Php84\NodeFactory;

use Argtyper202511\PhpParser\Node\PropertyHook;
use Argtyper202511\PhpParser\Node\Stmt\ClassMethod;
use Argtyper202511\PhpParser\Node\Stmt\Expression;
use Argtyper202511\PhpParser\Node\Stmt\Return_;
use Argtyper202511\RectorPrefix202511\Webmozart\Assert\Assert;
final class PropertyHookFactory
{
    public function create(ClassMethod $classMethod, string $propertyName): ?PropertyHook
    {
        $methodName = $classMethod->name->toString();
        if ($methodName === 'get' . ucfirst($propertyName)) {
            $methodName = 'get';
        } elseif ($methodName === 'set' . ucfirst($propertyName)) {
            $methodName = 'set';
        } else {
            return null;
        }
        Assert::notNull($classMethod->stmts);
        $soleStmt = $classMethod->stmts[0];
        // use sole Expr
        if (($soleStmt instanceof Expression || $soleStmt instanceof Return_) && $methodName !== 'set') {
            $body = $soleStmt->expr;
        } else {
            $body = [$soleStmt];
        }
        $setterPropertyHook = new PropertyHook($methodName, $body);
        $setterPropertyHook->params = $classMethod->params;
        return $setterPropertyHook;
    }
}
