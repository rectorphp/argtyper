<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Assert\NodeAnalyzer;

use Argtyper202511\PhpParser\Node\Expr\StaticCall;
use Argtyper202511\PhpParser\Node\Name;
use Argtyper202511\PhpParser\Node\Stmt\ClassMethod;
use Argtyper202511\PhpParser\Node\Stmt\Expression;
use Argtyper202511\PhpParser\PrettyPrinter\Standard;
use Argtyper202511\Rector\Assert\Enum\AssertClassName;
final class ExistingAssertStaticCallResolver
{
    /**
     * @return string[]
     */
    public function resolve(ClassMethod $classMethod) : array
    {
        if ($classMethod->stmts === null) {
            return [];
        }
        $existingAssertCallHashes = [];
        $standard = new Standard();
        foreach ($classMethod->stmts as $currentStmt) {
            if (!$currentStmt instanceof Expression) {
                continue;
            }
            if (!$currentStmt->expr instanceof StaticCall) {
                continue;
            }
            $staticCall = $currentStmt->expr;
            if (!$staticCall->class instanceof Name) {
                continue;
            }
            if (!\in_array($staticCall->class->toString(), [AssertClassName::WEBMOZART, AssertClassName::BEBERLEI], \true)) {
                continue;
            }
            $existingAssertCallHashes[] = $standard->prettyPrintExpr($staticCall);
        }
        return $existingAssertCallHashes;
    }
}
