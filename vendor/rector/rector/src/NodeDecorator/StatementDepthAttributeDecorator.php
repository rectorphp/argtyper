<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\NodeDecorator;

use Argtyper202511\PhpParser\Node\Stmt\ClassMethod;
use Argtyper202511\PhpParser\Node\Stmt\Expression;
use Argtyper202511\Rector\NodeTypeResolver\Node\AttributeKey;
final class StatementDepthAttributeDecorator
{
    /**
     * @param ClassMethod[] $classMethods
     */
    public static function decorateClassMethods(array $classMethods): void
    {
        foreach ($classMethods as $classMethod) {
            foreach ((array) $classMethod->stmts as $methodStmt) {
                $methodStmt->setAttribute(AttributeKey::IS_FIRST_LEVEL_STATEMENT, \true);
                if ($methodStmt instanceof Expression) {
                    $methodStmt->expr->setAttribute(AttributeKey::IS_FIRST_LEVEL_STATEMENT, \true);
                }
            }
        }
    }
}
