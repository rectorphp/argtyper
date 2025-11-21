<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\NodeAnalyzer;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr\New_;
use Argtyper202511\PhpParser\Node\Stmt\Class_;
final class ClassAnalyzer
{
    public function isAnonymousClass(Node $node) : bool
    {
        if ($node instanceof New_) {
            return $this->isAnonymousClass($node->class);
        }
        if ($node instanceof Class_) {
            return $node->isAnonymous();
        }
        return \false;
    }
}
