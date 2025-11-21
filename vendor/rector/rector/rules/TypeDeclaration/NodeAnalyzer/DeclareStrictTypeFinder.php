<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\TypeDeclaration\NodeAnalyzer;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Stmt\Declare_;
final class DeclareStrictTypeFinder
{
    public function hasDeclareStrictTypes(Node $node): bool
    {
        // when first node is Declare_, verify if there is strict_types definition already,
        // as multiple declare is allowed, with declare(strict_types=1) only allowed on very first node
        if (!$node instanceof Declare_) {
            return \false;
        }
        foreach ($node->declares as $declare) {
            if ($declare->key->toString() === 'strict_types') {
                return \true;
            }
        }
        return \false;
    }
}
