<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\BetterPhpDocParser\PhpDocInfo;

use Argtyper202511\PhpParser\Comment\Doc;
use Argtyper202511\PhpParser\Node;
use Argtyper202511\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use Argtyper202511\PHPStan\PhpDocParser\Lexer\Lexer;
use Argtyper202511\Rector\BetterPhpDocParser\Annotation\AnnotationNaming;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDocNodeFinder\PhpDocNodeByTypeFinder;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDocNodeMapper;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDocParser\BetterPhpDocParser;
use Argtyper202511\Rector\BetterPhpDocParser\ValueObject\Parser\BetterTokenIterator;
use Argtyper202511\Rector\BetterPhpDocParser\ValueObject\PhpDocAttributeKey;
use Argtyper202511\Rector\BetterPhpDocParser\ValueObject\StartAndEnd;
use Argtyper202511\Rector\NodeTypeResolver\Node\AttributeKey;
use Argtyper202511\Rector\StaticTypeMapper\StaticTypeMapper;
final class PhpDocInfoFactory
{
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocNodeMapper
     */
    private $phpDocNodeMapper;
    /**
     * @readonly
     * @var \PHPStan\PhpDocParser\Lexer\Lexer
     */
    private $lexer;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocParser\BetterPhpDocParser
     */
    private $betterPhpDocParser;
    /**
     * @readonly
     * @var \Rector\StaticTypeMapper\StaticTypeMapper
     */
    private $staticTypeMapper;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\Annotation\AnnotationNaming
     */
    private $annotationNaming;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocNodeFinder\PhpDocNodeByTypeFinder
     */
    private $phpDocNodeByTypeFinder;
    /**
     * @var array<int, PhpDocInfo>
     */
    private $phpDocInfosByObjectId = [];
    public function __construct(PhpDocNodeMapper $phpDocNodeMapper, Lexer $lexer, BetterPhpDocParser $betterPhpDocParser, StaticTypeMapper $staticTypeMapper, AnnotationNaming $annotationNaming, PhpDocNodeByTypeFinder $phpDocNodeByTypeFinder)
    {
        $this->phpDocNodeMapper = $phpDocNodeMapper;
        $this->lexer = $lexer;
        $this->betterPhpDocParser = $betterPhpDocParser;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->annotationNaming = $annotationNaming;
        $this->phpDocNodeByTypeFinder = $phpDocNodeByTypeFinder;
    }
    public function createFromNodeOrEmpty(Node $node): \Argtyper202511\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo
    {
        // already added
        $phpDocInfo = $node->getAttribute(AttributeKey::PHP_DOC_INFO);
        if ($phpDocInfo instanceof \Argtyper202511\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo) {
            return $phpDocInfo;
        }
        $phpDocInfo = $this->createFromNode($node);
        if ($phpDocInfo instanceof \Argtyper202511\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo) {
            return $phpDocInfo;
        }
        return $this->createEmpty($node);
    }
    public function createFromNode(Node $node): ?\Argtyper202511\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo
    {
        $objectId = spl_object_id($node);
        if (isset($this->phpDocInfosByObjectId[$objectId])) {
            return $this->phpDocInfosByObjectId[$objectId];
        }
        $docComment = $node->getDocComment();
        if (!$docComment instanceof Doc) {
            if ($node->getComments() === []) {
                return null;
            }
            // create empty node
            $tokenIterator = new BetterTokenIterator([]);
            $phpDocNode = new PhpDocNode([]);
        } else {
            $tokens = $this->lexer->tokenize($docComment->getText());
            $tokenIterator = new BetterTokenIterator($tokens);
            $phpDocNode = $this->betterPhpDocParser->parseWithNode($tokenIterator, $node);
            $this->setPositionOfLastToken($phpDocNode);
        }
        $phpDocInfo = $this->createFromPhpDocNode($phpDocNode, $tokenIterator, $node);
        $this->phpDocInfosByObjectId[$objectId] = $phpDocInfo;
        return $phpDocInfo;
    }
    /**
     * @api downgrade
     */
    public function createEmpty(Node $node): \Argtyper202511\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo
    {
        $phpDocNode = new PhpDocNode([]);
        $phpDocInfo = $this->createFromPhpDocNode($phpDocNode, new BetterTokenIterator([]), $node);
        // multiline by default
        $phpDocInfo->makeMultiLined();
        return $phpDocInfo;
    }
    /**
     * Needed for printing
     */
    private function setPositionOfLastToken(PhpDocNode $phpDocNode): void
    {
        if ($phpDocNode->children === []) {
            return;
        }
        $phpDocChildNodes = $phpDocNode->children;
        $phpDocChildNode = array_pop($phpDocChildNodes);
        $startAndEnd = $phpDocChildNode->getAttribute(PhpDocAttributeKey::START_AND_END);
        if ($startAndEnd instanceof StartAndEnd) {
            $phpDocNode->setAttribute(PhpDocAttributeKey::LAST_PHP_DOC_TOKEN_POSITION, $startAndEnd->getEnd());
        }
    }
    private function createFromPhpDocNode(PhpDocNode $phpDocNode, BetterTokenIterator $betterTokenIterator, Node $node): \Argtyper202511\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo
    {
        $this->phpDocNodeMapper->transform($phpDocNode, $betterTokenIterator);
        $phpDocInfo = new \Argtyper202511\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo($phpDocNode, $betterTokenIterator, $this->staticTypeMapper, $node, $this->annotationNaming, $this->phpDocNodeByTypeFinder);
        $node->setAttribute(AttributeKey::PHP_DOC_INFO, $phpDocInfo);
        return $phpDocInfo;
    }
}
