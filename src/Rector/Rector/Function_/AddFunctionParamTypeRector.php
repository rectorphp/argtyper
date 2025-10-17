<?php

declare(strict_types=1);

namespace Rector\ArgTyper\Rector\Rector\Function_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Function_;
use Rector\ArgTyper\Configuration\FuncCallTypesConfigurationProvider;
use Rector\Rector\AbstractRector;

/**
 * @see \Rector\ArgTyper\Tests\Rector\Rector\Function_\AddFunctionParamTypeRector\AddFunctionParamTypeRectorTest
 */
final class AddFunctionParamTypeRector extends AbstractRector
{
    public function __construct(
        private readonly FuncCallTypesConfigurationProvider $funcCallTypesConfigurationProvider,
    ) {
    }

    public function getNodeTypes(): array
    {
        return [Function_::class];
    }

    /**
     * @param Function_ $node
     */
    public function refactor(Node $node)
    {
        if ($node->getParams() === []) {
            return null;
        }

        $hasChanged = false;

        foreach ($node->getParams() as $position => $param) {
            // already filled
            if ($param->type instanceof Node) {
                continue;
            }

            $functionTypesByPosition = $this->funcCallTypesConfigurationProvider->matchByPosition($node);
            if ($functionTypesByPosition === []) {
                continue;
            }

            $paramFunctionTypes = $functionTypesByPosition[$position] ?? null;
            if ($paramFunctionTypes === null) {
                continue;
            }

            $hasChanged = true;
            dump($paramFunctionTypes);
            die;
        }

        if (! $hasChanged) {
            return null;
        }

        return $node;
    }
}
