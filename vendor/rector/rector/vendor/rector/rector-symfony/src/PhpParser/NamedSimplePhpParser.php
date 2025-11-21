<?php

declare (strict_types=1);
namespace Rector\Symfony\PhpParser;

use Argtyper202511\PhpParser\Node\Stmt;
use Argtyper202511\PhpParser\NodeTraverser;
use Argtyper202511\PhpParser\NodeVisitor\NameResolver;
use Argtyper202511\PhpParser\Parser;
use Argtyper202511\PhpParser\ParserFactory;
final class NamedSimplePhpParser
{
    /**
     * @readonly
     * @var \PhpParser\Parser
     */
    private $phpParser;
    public function __construct()
    {
        $parserFactory = new ParserFactory();
        $this->phpParser = $parserFactory->createForNewestSupportedVersion();
    }
    /**
     * @return Stmt[]
     */
    public function parseString(string $content): array
    {
        $stmts = $this->phpParser->parse($content);
        if ($stmts === null) {
            return [];
        }
        $nodeTraverser = new NodeTraverser();
        $nodeTraverser->addVisitor(new NameResolver());
        $nodeTraverser->traverse($stmts);
        return $stmts;
    }
}
