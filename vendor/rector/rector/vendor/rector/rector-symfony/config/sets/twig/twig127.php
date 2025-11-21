<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Argtyper202511\Rector\Renaming\ValueObject\MethodCallRename;
return RectorConfig::configure()->withConfiguredRule(RenameMethodRector::class, [new MethodCallRename('Twig_Node', 'getLine', 'getTemplateLine'), new MethodCallRename('Twig_Node', 'getFilename', 'getTemplateName'), new MethodCallRename('Twig_Template', 'getSource', 'getSourceContext'), new MethodCallRename('Twig_Error', 'getTemplateFile', 'getTemplateName'), new MethodCallRename('Twig_Error', 'getTemplateName', 'setTemplateName')]);
