<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\BetterPhpDocParser\PhpDocNodeVisitor;

use Argtyper202511\PHPStan\PhpDocParser\Ast\Node;
use Argtyper202511\PHPStan\PhpDocParser\Ast\Type\CallableTypeNode;
use Argtyper202511\Rector\BetterPhpDocParser\Attributes\AttributeMirrorer;
use Argtyper202511\Rector\BetterPhpDocParser\Contract\BasePhpDocNodeVisitorInterface;
use Argtyper202511\Rector\BetterPhpDocParser\ValueObject\Type\SpacingAwareCallableTypeNode;
use Argtyper202511\Rector\PhpDocParser\PhpDocParser\PhpDocNodeVisitor\AbstractPhpDocNodeVisitor;
final class CallableTypePhpDocNodeVisitor extends AbstractPhpDocNodeVisitor implements BasePhpDocNodeVisitorInterface
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
        if (!$node instanceof CallableTypeNode) {
            return null;
        }
        if ($node instanceof SpacingAwareCallableTypeNode) {
            return null;
        }
        $spacingAwareCallableTypeNode = new SpacingAwareCallableTypeNode($node->identifier, $node->parameters, $node->returnType, []);
        $this->attributeMirrorer->mirror($node, $spacingAwareCallableTypeNode);
        return $spacingAwareCallableTypeNode;
    }
}
