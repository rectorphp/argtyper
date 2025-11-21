<?php

declare (strict_types=1);
namespace Rector\ArgTyper\Rector\TypeMapper;

use Argtyper202511\PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use Argtyper202511\PHPStan\PhpDocParser\Lexer\Lexer;
use Argtyper202511\PHPStan\PhpDocParser\Parser\ConstExprParser;
use Argtyper202511\PHPStan\PhpDocParser\Parser\PhpDocParser;
use Argtyper202511\PHPStan\PhpDocParser\Parser\TokenIterator;
use Argtyper202511\PHPStan\PhpDocParser\Parser\TypeParser;
use Argtyper202511\PHPStan\PhpDocParser\ParserConfig;
/**
 * @todo possibly move to Rector core to StaticTypeMapper
 */
final class DocStringTypeMapper
{
    public function mapToTypeNode(string $typeString) : ?\Argtyper202511\PHPStan\PhpDocParser\Ast\Type\TypeNode
    {
        $parserConfig = new ParserConfig([]);
        $lexer = new Lexer($parserConfig);
        $tokens = $lexer->tokenize('@param ' . $typeString . '$someParam');
        $constExprParser = new ConstExprParser($parserConfig);
        $typeParser = new TypeParser($parserConfig, $constExprParser);
        $phpDocParser = new PhpDocParser($parserConfig, $typeParser, $constExprParser);
        $phpDocTagNode = $phpDocParser->parseTag(new TokenIterator($tokens));
        if (!$phpDocTagNode->value instanceof ParamTagValueNode) {
            return null;
        }
        return $phpDocTagNode->value->type;
    }
}
