<?php

declare (strict_types=1);
namespace RectorPrefix202511\Doctrine\Inflector;

use RectorPrefix202511\Doctrine\Inflector\Rules\English;
use RectorPrefix202511\Doctrine\Inflector\Rules\Esperanto;
use RectorPrefix202511\Doctrine\Inflector\Rules\French;
use RectorPrefix202511\Doctrine\Inflector\Rules\Italian;
use RectorPrefix202511\Doctrine\Inflector\Rules\NorwegianBokmal;
use RectorPrefix202511\Doctrine\Inflector\Rules\Portuguese;
use RectorPrefix202511\Doctrine\Inflector\Rules\Spanish;
use RectorPrefix202511\Doctrine\Inflector\Rules\Turkish;
use InvalidArgumentException;
use function sprintf;
final class InflectorFactory
{
    public static function create(): \RectorPrefix202511\Doctrine\Inflector\LanguageInflectorFactory
    {
        return self::createForLanguage(\RectorPrefix202511\Doctrine\Inflector\Language::ENGLISH);
    }
    public static function createForLanguage(string $language): \RectorPrefix202511\Doctrine\Inflector\LanguageInflectorFactory
    {
        switch ($language) {
            case \RectorPrefix202511\Doctrine\Inflector\Language::ENGLISH:
                return new English\InflectorFactory();
            case \RectorPrefix202511\Doctrine\Inflector\Language::ESPERANTO:
                return new Esperanto\InflectorFactory();
            case \RectorPrefix202511\Doctrine\Inflector\Language::FRENCH:
                return new French\InflectorFactory();
            case \RectorPrefix202511\Doctrine\Inflector\Language::ITALIAN:
                return new Italian\InflectorFactory();
            case \RectorPrefix202511\Doctrine\Inflector\Language::NORWEGIAN_BOKMAL:
                return new NorwegianBokmal\InflectorFactory();
            case \RectorPrefix202511\Doctrine\Inflector\Language::PORTUGUESE:
                return new Portuguese\InflectorFactory();
            case \RectorPrefix202511\Doctrine\Inflector\Language::SPANISH:
                return new Spanish\InflectorFactory();
            case \RectorPrefix202511\Doctrine\Inflector\Language::TURKISH:
                return new Turkish\InflectorFactory();
            default:
                throw new InvalidArgumentException(sprintf('Language "%s" is not supported.', $language));
        }
    }
}
