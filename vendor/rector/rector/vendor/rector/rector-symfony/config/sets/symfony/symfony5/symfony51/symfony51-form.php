<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\Renaming\Rector\ClassConstFetch\RenameClassConstFetchRector;
use Argtyper202511\Rector\Renaming\Rector\Name\RenameClassRector;
use Argtyper202511\Rector\Renaming\ValueObject\RenameClassAndConstFetch;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, ['Argtyper202511\\Symfony\\Component\\Form\\Extension\\Validator\\Util\\ServerParams' => 'Argtyper202511\\Symfony\\Component\\Form\\Util\\ServerParams']);
    $rectorConfig->ruleWithConfiguration(RenameClassConstFetchRector::class, [new RenameClassAndConstFetch('Argtyper202511\\Symfony\\Component\\Form\\Extension\\Core\\DataTransformer\\NumberToLocalizedStringTransformer', 'ROUND_FLOOR', 'NumberFormatter', 'ROUND_FLOOR'), new RenameClassAndConstFetch('Argtyper202511\\Symfony\\Component\\Form\\Extension\\Core\\DataTransformer\\NumberToLocalizedStringTransformer', 'ROUND_DOWN', 'NumberFormatter', 'ROUND_DOWN'), new RenameClassAndConstFetch('Argtyper202511\\Symfony\\Component\\Form\\Extension\\Core\\DataTransformer\\NumberToLocalizedStringTransformer', 'ROUND_HALF_DOWN', 'NumberFormatter', 'ROUND_HALFDOWN'), new RenameClassAndConstFetch('Argtyper202511\\Symfony\\Component\\Form\\Extension\\Core\\DataTransformer\\NumberToLocalizedStringTransformer', 'ROUND_HALF_EVEN', 'NumberFormatter', 'ROUND_HALFEVEN'), new RenameClassAndConstFetch('Argtyper202511\\Symfony\\Component\\Form\\Extension\\Core\\DataTransformer\\NumberToLocalizedStringTransformer', 'ROUND_HALFUP', 'NumberFormatter', 'ROUND_HALFUP'), new RenameClassAndConstFetch('Argtyper202511\\Symfony\\Component\\Form\\Extension\\Core\\DataTransformer\\NumberToLocalizedStringTransformer', 'ROUND_UP', 'NumberFormatter', 'ROUND_UP'), new RenameClassAndConstFetch('Argtyper202511\\Symfony\\Component\\Form\\Extension\\Core\\DataTransformer\\NumberToLocalizedStringTransformer', 'ROUND_CEILING', 'NumberFormatter', 'ROUND_CEILING')]);
};
