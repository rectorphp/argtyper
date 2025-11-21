<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\CodingStyle\ClassNameImport\ClassNameImportSkipVoter;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\Rector\CodingStyle\Contract\ClassNameImport\ClassNameImportSkipVoterInterface;
use Argtyper202511\Rector\Configuration\Option;
use Argtyper202511\Rector\Configuration\Parameter\SimpleParameterProvider;
use Argtyper202511\Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
use Argtyper202511\Rector\ValueObject\Application\File;
final class ShortClassImportSkipVoter implements ClassNameImportSkipVoterInterface
{
    public function shouldSkip(File $file, FullyQualifiedObjectType $fullyQualifiedObjectType, Node $node): bool
    {
        $className = ltrim($fullyQualifiedObjectType->getClassName(), '\\');
        if (substr_count($className, '\\') === 0) {
            return !SimpleParameterProvider::provideBoolParameter(Option::IMPORT_SHORT_CLASSES);
        }
        return \false;
    }
}
