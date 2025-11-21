<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\CodingStyle\Contract\ClassNameImport;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
use Argtyper202511\Rector\ValueObject\Application\File;
interface ClassNameImportSkipVoterInterface
{
    public function shouldSkip(File $file, FullyQualifiedObjectType $fullyQualifiedObjectType, Node $node) : bool;
}
