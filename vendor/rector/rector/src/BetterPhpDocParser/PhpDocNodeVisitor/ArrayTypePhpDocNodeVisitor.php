<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\BetterPhpDocParser\PhpDocNodeVisitor;

use Argtyper202511\PHPStan\PhpDocParser\Ast\Node;
use Argtyper202511\PHPStan\PhpDocParser\Ast\Type\ArrayTypeNode;
use Argtyper202511\Rector\BetterPhpDocParser\Attributes\AttributeMirrorer;
use Argtyper202511\Rector\BetterPhpDocParser\Contract\BasePhpDocNodeVisitorInterface;
use Argtyper202511\Rector\BetterPhpDocParser\ValueObject\Type\SpacingAwareArrayTypeNode;
use Argtyper202511\Rector\PhpDocParser\PhpDocParser\PhpDocNodeVisitor\AbstractPhpDocNodeVisitor;
final class ArrayTypePhpDocNodeVisitor extends AbstractPhpDocNodeVisitor implements BasePhpDocNodeVisitorInterface
{
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\Attributes\AttributeMirrorer
     */
    private $attributeMirrorer;
    public function __construct(AttributeMirrorer $attributeMirrorer)
    {
        $this->attributeMirrorer = $attributeMirrorer;
    }
    public function enterNode(Node $node) : ?Node
    {
        if (!$node instanceof ArrayTypeNode) {
            return null;
        }
        if ($node instanceof SpacingAwareArrayTypeNode) {
            return null;
        }
        $spacingAwareArrayTypeNode = new SpacingAwareArrayTypeNode($node->type);
        $this->attributeMirrorer->mirror($node, $spacingAwareArrayTypeNode);
        return $spacingAwareArrayTypeNode;
    }
}
