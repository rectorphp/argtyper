<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\CodingStyle\ClassNameImport\ClassNameImportSkipVoter;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\Rector\CodingStyle\Contract\ClassNameImport\ClassNameImportSkipVoterInterface;
use Argtyper202511\Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
use Argtyper202511\Rector\ValueObject\Application\File;
final class ReservedClassNameImportSkipVoter implements ClassNameImportSkipVoterInterface
{
    /**
     * @var string[]
     */
    private const RESERVED_CLASS_NAMES = ['bool', 'false', 'float', 'int', 'null', 'parent', 'self', 'static', 'string', 'true', 'void', 'never', 'iterable', 'object', 'mixed', 'array', 'callable'];
    public function shouldSkip(File $file, FullyQualifiedObjectType $fullyQualifiedObjectType, Node $node) : bool
    {
        $shortName = $fullyQualifiedObjectType->getShortNameLowered();
        return \in_array($shortName, self::RESERVED_CLASS_NAMES, \true);
    }
}
