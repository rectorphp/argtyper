<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Php80\NodeAnalyzer;

use Argtyper202511\PhpParser\Node\Param;
use Argtyper202511\PhpParser\Node\Stmt\Class_;
use Argtyper202511\PhpParser\Node\Stmt\ClassMethod;
use Argtyper202511\Rector\ValueObject\MethodName;
final class PromotedPropertyResolver
{
    /**
     * @return Param[]
     */
    public function resolveFromClass(Class_ $class): array
    {
        $constructClassMethod = $class->getMethod(MethodName::CONSTRUCT);
        if (!$constructClassMethod instanceof ClassMethod) {
            return [];
        }
        $promotedPropertyParams = [];
        foreach ($constructClassMethod->getParams() as $param) {
            if (!$param->isPromoted()) {
                continue;
            }
            $promotedPropertyParams[] = $param;
        }
        return $promotedPropertyParams;
    }
}
