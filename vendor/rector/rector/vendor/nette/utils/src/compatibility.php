<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace RectorPrefix202511\Nette\Utils;

use RectorPrefix202511\Nette;
if (\false) {
    /** @deprecated use Nette\HtmlStringable */
    interface IHtmlString extends Nette\HtmlStringable
    {
    }
} elseif (!interface_exists(\RectorPrefix202511\Nette\Utils\IHtmlString::class)) {
    class_alias(Nette\HtmlStringable::class, \RectorPrefix202511\Nette\Utils\IHtmlString::class);
}
namespace RectorPrefix202511\Nette\Localization;

if (\false) {
    /** @deprecated use Nette\Localization\Translator */
    interface ITranslator extends \RectorPrefix202511\Nette\Localization\Translator
    {
    }
} elseif (!interface_exists(\RectorPrefix202511\Nette\Localization\ITranslator::class)) {
    class_alias(\RectorPrefix202511\Nette\Localization\Translator::class, \RectorPrefix202511\Nette\Localization\ITranslator::class);
}
