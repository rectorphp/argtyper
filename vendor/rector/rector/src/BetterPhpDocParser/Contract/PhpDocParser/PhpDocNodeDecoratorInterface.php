<?php

declare (strict_types=1);
namespace Rector\BetterPhpDocParser\Contract\PhpDocParser;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
interface PhpDocNodeDecoratorInterface
{
    public function decorate(PhpDocNode $phpDocNode, Node $phpNode): void;
}
