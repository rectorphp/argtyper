<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\Php73\Rector\BooleanOr\IsCountableRector;
use Argtyper202511\Rector\Php73\Rector\ConstFetch\SensitiveConstantNameRector;
use Argtyper202511\Rector\Php73\Rector\FuncCall\ArrayKeyFirstLastRector;
use Argtyper202511\Rector\Php73\Rector\FuncCall\RegexDashEscapeRector;
use Argtyper202511\Rector\Php73\Rector\FuncCall\SensitiveDefineRector;
use Argtyper202511\Rector\Php73\Rector\FuncCall\SetCookieRector;
use Argtyper202511\Rector\Php73\Rector\FuncCall\StringifyStrNeedlesRector;
use Argtyper202511\Rector\Php73\Rector\String_\SensitiveHereNowDocRector;
use Argtyper202511\Rector\Renaming\Rector\FuncCall\RenameFunctionRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(RenameFunctionRector::class, [
        # https://wiki.php.net/rfc/deprecations_php_7_3
        'image2wbmp' => 'imagewbmp',
        'mbregex_encoding' => 'mb_regex_encoding',
        'mbereg' => 'mb_ereg',
        'mberegi' => 'mb_eregi',
        'mbereg_replace' => 'mb_ereg_replace',
        'mberegi_replace' => 'mb_eregi_replace',
        'mbsplit' => 'mb_split',
        'mbereg_match' => 'mb_ereg_match',
        'mbereg_search' => 'mb_ereg_search',
        'mbereg_search_pos' => 'mb_ereg_search_pos',
        'mbereg_search_regs' => 'mb_ereg_search_regs',
        'mbereg_search_init' => 'mb_ereg_search_init',
        'mbereg_search_getregs' => 'mb_ereg_search_getregs',
        'mbereg_search_getpos' => 'mb_ereg_search_getpos',
    ]);
    $rectorConfig->rules([StringifyStrNeedlesRector::class, RegexDashEscapeRector::class, SetCookieRector::class, IsCountableRector::class, ArrayKeyFirstLastRector::class, SensitiveDefineRector::class, SensitiveConstantNameRector::class, SensitiveHereNowDocRector::class]);
};
