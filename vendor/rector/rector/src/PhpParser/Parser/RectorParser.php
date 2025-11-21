<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\PhpParser\Parser;

use Argtyper202511\PhpParser\Node\Stmt;
use Argtyper202511\PhpParser\ParserFactory;
use Argtyper202511\PhpParser\PhpVersion;
use Argtyper202511\PHPStan\Parser\Parser;
use Argtyper202511\Rector\PhpParser\ValueObject\StmtsAndTokens;
use Argtyper202511\Rector\Util\Reflection\PrivatesAccessor;
final class RectorParser
{
    /**
     * @readonly
     * @var \PHPStan\Parser\Parser
     */
    private $parser;
    /**
     * @readonly
     * @var \Rector\Util\Reflection\PrivatesAccessor
     */
    private $privatesAccessor;
    public function __construct(Parser $parser, PrivatesAccessor $privatesAccessor)
    {
        $this->parser = $parser;
        $this->privatesAccessor = $privatesAccessor;
    }
    /**
     * @api used by rector-symfony
     *
     * @return Stmt[]
     */
    public function parseFile(string $filePath) : array
    {
        return $this->parser->parseFile($filePath);
    }
    /**
     * @return Stmt[]
     */
    public function parseString(string $fileContent) : array
    {
        return $this->parser->parseString($fileContent);
    }
    public function parseFileContentToStmtsAndTokens(string $fileContent, bool $forNewestSupportedVersion = \true) : StmtsAndTokens
    {
        if (!$forNewestSupportedVersion) {
            // don't directly change PHPStan Parser service
            // to avoid reuse on next file
            $phpstanParser = clone $this->parser;
            $parserFactory = new ParserFactory();
            $parser = $parserFactory->createForVersion(PhpVersion::fromString('7.0'));
            $this->privatesAccessor->setPrivateProperty($phpstanParser, 'parser', $parser);
            return $this->resolveStmtsAndTokens($phpstanParser, $fileContent);
        }
        return $this->resolveStmtsAndTokens($this->parser, $fileContent);
    }
    private function resolveStmtsAndTokens(Parser $parser, string $fileContent) : StmtsAndTokens
    {
        $stmts = $parser->parseString($fileContent);
        $innerParser = $this->privatesAccessor->getPrivateProperty($parser, 'parser');
        $tokens = $innerParser->getTokens();
        return new StmtsAndTokens($stmts, $tokens);
    }
}
