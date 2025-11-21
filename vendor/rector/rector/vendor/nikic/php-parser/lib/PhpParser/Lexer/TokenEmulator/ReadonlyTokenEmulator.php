<?php

declare (strict_types=1);
namespace Argtyper202511\PhpParser\Lexer\TokenEmulator;

use Argtyper202511\PhpParser\PhpVersion;
final class ReadonlyTokenEmulator extends \Argtyper202511\PhpParser\Lexer\TokenEmulator\KeywordEmulator
{
    public function getPhpVersion(): PhpVersion
    {
        return PhpVersion::fromComponents(8, 1);
    }
    public function getKeywordString(): string
    {
        return 'readonly';
    }
    public function getKeywordToken(): int
    {
        return \T_READONLY;
    }
    protected function isKeywordContext(array $tokens, int $pos): bool
    {
        if (!parent::isKeywordContext($tokens, $pos)) {
            return \false;
        }
        // Support "function readonly("
        return !(isset($tokens[$pos + 1]) && ($tokens[$pos + 1]->text === '(' || $tokens[$pos + 1]->id === \T_WHITESPACE && isset($tokens[$pos + 2]) && $tokens[$pos + 2]->text === '('));
    }
}
