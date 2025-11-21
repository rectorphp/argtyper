<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\BetterPhpDocParser;

use Argtyper202511\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use Argtyper202511\Rector\BetterPhpDocParser\Contract\BasePhpDocNodeVisitorInterface;
use Argtyper202511\Rector\BetterPhpDocParser\DataProvider\CurrentTokenIteratorProvider;
use Argtyper202511\Rector\BetterPhpDocParser\ValueObject\Parser\BetterTokenIterator;
use Argtyper202511\Rector\PhpDocParser\PhpDocParser\PhpDocNodeTraverser;
use Argtyper202511\Rector\PhpDocParser\PhpDocParser\PhpDocNodeVisitor\CloningPhpDocNodeVisitor;
use Argtyper202511\Rector\PhpDocParser\PhpDocParser\PhpDocNodeVisitor\ParentConnectingPhpDocNodeVisitor;
use Argtyper202511\RectorPrefix202511\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\BetterPhpDocParser\PhpDocNodeMapperTest
 */
final class PhpDocNodeMapper
{
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\DataProvider\CurrentTokenIteratorProvider
     */
    private $currentTokenIteratorProvider;
    /**
     * @var BasePhpDocNodeVisitorInterface[]
     * @readonly
     */
    private $phpDocNodeVisitors;
    /**
     * @readonly
     * @var \Rector\PhpDocParser\PhpDocParser\PhpDocNodeTraverser
     */
    private $phpDocNodeTraverser;
    /**
     * @param BasePhpDocNodeVisitorInterface[] $phpDocNodeVisitors
     */
    public function __construct(CurrentTokenIteratorProvider $currentTokenIteratorProvider, ParentConnectingPhpDocNodeVisitor $parentConnectingPhpDocNodeVisitor, CloningPhpDocNodeVisitor $cloningPhpDocNodeVisitor, array $phpDocNodeVisitors)
    {
        $this->currentTokenIteratorProvider = $currentTokenIteratorProvider;
        $this->phpDocNodeVisitors = $phpDocNodeVisitors;
        Assert::notEmpty($phpDocNodeVisitors);
        $this->phpDocNodeTraverser = new PhpDocNodeTraverser();
        $this->phpDocNodeTraverser->addPhpDocNodeVisitor($parentConnectingPhpDocNodeVisitor);
        $this->phpDocNodeTraverser->addPhpDocNodeVisitor($cloningPhpDocNodeVisitor);
        foreach ($this->phpDocNodeVisitors as $phpDocNodeVisitor) {
            $this->phpDocNodeTraverser->addPhpDocNodeVisitor($phpDocNodeVisitor);
        }
    }
    public function transform(PhpDocNode $phpDocNode, BetterTokenIterator $betterTokenIterator) : void
    {
        $this->currentTokenIteratorProvider->setBetterTokenIterator($betterTokenIterator);
        $this->phpDocNodeTraverser->traverse($phpDocNode);
    }
}
