<?php

declare(strict_types=1);

namespace Rector\ArgTyper\Rector\TypeMapper;

use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\ConstExprParser;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use PHPStan\PhpDocParser\Parser\TypeParser;
use PHPStan\PhpDocParser\ParserConfig;

/**
 * @todo possibly move to Rector core to StaticTypeMapper
 */
final class DocStringTypeMapper
{
    private function mapToTypeNode(string $typeString): ?\PHPStan\PhpDocParser\Ast\Type\TypeNode
    {
        $config = new ParserConfig(usedAttributes: []);
        $lexer = new Lexer($config);
        $tokens = $lexer->tokenize('@param ' . $typeString . '$someParam');

        $constExprParser = new ConstExprParser($config);
        $typeParser = new TypeParser($config, $constExprParser);

        $phpDocParser = new PhpDocParser($config, $typeParser, $constExprParser);

        $phpDocTagNode = $phpDocParser->parseTag(new TokenIterator($tokens));
        if (! $phpDocTagNode->value instanceof ParamTagValueNode) {
            return null;
        }

        return $phpDocTagNode->value->type;
    }

}