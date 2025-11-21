<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\BetterPhpDocParser\PhpDocNodeVisitor;

use Argtyper202511\PHPStan\PhpDocParser\Ast\Attribute;
use Argtyper202511\PHPStan\PhpDocParser\Ast\Node;
use Argtyper202511\PHPStan\PhpDocParser\Ast\PhpDoc\TemplateTagValueNode;
use Argtyper202511\PHPStan\PhpDocParser\Lexer\Lexer;
use Argtyper202511\Rector\BetterPhpDocParser\Attributes\AttributeMirrorer;
use Argtyper202511\Rector\BetterPhpDocParser\Contract\BasePhpDocNodeVisitorInterface;
use Argtyper202511\Rector\BetterPhpDocParser\DataProvider\CurrentTokenIteratorProvider;
use Argtyper202511\Rector\BetterPhpDocParser\ValueObject\Parser\BetterTokenIterator;
use Argtyper202511\Rector\BetterPhpDocParser\ValueObject\PhpDoc\SpacingAwareTemplateTagValueNode;
use Argtyper202511\Rector\Exception\ShouldNotHappenException;
use Argtyper202511\Rector\PhpDocParser\PhpDocParser\PhpDocNodeVisitor\AbstractPhpDocNodeVisitor;
final class TemplatePhpDocNodeVisitor extends AbstractPhpDocNodeVisitor implements BasePhpDocNodeVisitorInterface
{
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\DataProvider\CurrentTokenIteratorProvider
     */
    private $currentTokenIteratorProvider;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\Attributes\AttributeMirrorer
     */
    private $attributeMirrorer;
    public function __construct(CurrentTokenIteratorProvider $currentTokenIteratorProvider, AttributeMirrorer $attributeMirrorer)
    {
        $this->currentTokenIteratorProvider = $currentTokenIteratorProvider;
        $this->attributeMirrorer = $attributeMirrorer;
    }
    public function enterNode(Node $node) : ?Node
    {
        if (!$node instanceof TemplateTagValueNode) {
            return null;
        }
        if ($node instanceof SpacingAwareTemplateTagValueNode) {
            return null;
        }
        $betterTokenIterator = $this->currentTokenIteratorProvider->provide();
        $startIndex = $node->getAttribute(Attribute::START_INDEX);
        $endIndex = $node->getAttribute(Attribute::END_INDEX);
        if ($startIndex === null || $endIndex === null) {
            throw new ShouldNotHappenException();
        }
        $prepositions = $this->resolvePreposition($betterTokenIterator, $startIndex, $endIndex);
        $spacingAwareTemplateTagValueNode = new SpacingAwareTemplateTagValueNode($node->name, $node->bound, $node->description, $prepositions);
        $this->attributeMirrorer->mirror($node, $spacingAwareTemplateTagValueNode);
        return $spacingAwareTemplateTagValueNode;
    }
    private function resolvePreposition(BetterTokenIterator $betterTokenIterator, int $startIndex, int $endIndex) : string
    {
        $partialTokens = $betterTokenIterator->partialTokens($startIndex, $endIndex);
        foreach ($partialTokens as $partialToken) {
            if ($partialToken[1] !== Lexer::TOKEN_IDENTIFIER) {
                continue;
            }
            if (!\in_array($partialToken[0], ['as', 'of'], \true)) {
                continue;
            }
            return $partialToken[0];
        }
        return 'of';
    }
}
