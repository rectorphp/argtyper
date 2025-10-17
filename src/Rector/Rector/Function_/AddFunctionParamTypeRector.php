<?php

declare(strict_types=1);

namespace Rector\ArgTyper\Rector\Rector\Function_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Function_;
use Rector\Rector\AbstractRector;

/**
 * @see \Rector\ArgTyper\Tests\Rector\Rector\Function_\AddFunctionParamTypeRector\AddFunctionParamTypeRectorTest
 */
final class AddFunctionParamTypeRector extends AbstractRector
{
    public function getNodeTypes(): array
    {
        return [Function_::class];
    }

    /**
     * @param Function_ $node
     */
    public function refactor(Node $node)
    {

        // ...
        dump('...');
        die;
    }
}
