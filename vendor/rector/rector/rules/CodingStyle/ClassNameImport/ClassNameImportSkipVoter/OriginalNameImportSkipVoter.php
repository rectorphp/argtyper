<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\CodingStyle\ClassNameImport\ClassNameImportSkipVoter;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Name;
use Argtyper202511\PhpParser\Node\Name\FullyQualified;
use Argtyper202511\Rector\CodingStyle\Contract\ClassNameImport\ClassNameImportSkipVoterInterface;
use Argtyper202511\Rector\NodeTypeResolver\Node\AttributeKey;
use Argtyper202511\Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
use Argtyper202511\Rector\ValueObject\Application\File;
final class OriginalNameImportSkipVoter implements ClassNameImportSkipVoterInterface
{
    public function shouldSkip(File $file, FullyQualifiedObjectType $fullyQualifiedObjectType, Node $node): bool
    {
        if (!$node instanceof FullyQualified) {
            return \false;
        }
        if (substr_count($node->toCodeString(), '\\') === 1) {
            return \false;
        }
        // verify long name, as short name verify may conflict
        // see test PR: https://github.com/rectorphp/rector-src/pull/6208
        // ref https://3v4l.org/21H5j vs https://3v4l.org/GIHSB
        $originalName = $node->getAttribute(AttributeKey::ORIGINAL_NAME);
        return $originalName instanceof Name && $originalName->getLast() === $originalName->toString();
    }
}
