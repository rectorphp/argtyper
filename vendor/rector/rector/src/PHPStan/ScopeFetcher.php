<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\PHPStan;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PHPStan\Analyser\MutatingScope;
use Argtyper202511\PHPStan\Analyser\Scope;
use Argtyper202511\Rector\Exception\ShouldNotHappenException;
use Argtyper202511\Rector\NodeTypeResolver\Node\AttributeKey;
final class ScopeFetcher
{
    public static function fetch(Node $node) : Scope
    {
        /** @var MutatingScope|null $currentScope */
        $currentScope = $node->getAttribute(AttributeKey::SCOPE);
        if (!$currentScope instanceof Scope) {
            $errorMessage = \sprintf('Scope not available on "%s" node. Fix scope refresh on changed nodes first', \get_class($node));
            throw new ShouldNotHappenException($errorMessage);
        }
        return $currentScope;
    }
}
