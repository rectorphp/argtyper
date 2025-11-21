<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\CodingStyle\ClassNameImport\ClassNameImportSkipVoter;

use Argtyper202511\RectorPrefix202511\Nette\Utils\Strings;
use Argtyper202511\PhpParser\Node;
use Argtyper202511\Rector\CodingStyle\ClassNameImport\ShortNameResolver;
use Argtyper202511\Rector\CodingStyle\Contract\ClassNameImport\ClassNameImportSkipVoterInterface;
use Argtyper202511\Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
use Argtyper202511\Rector\ValueObject\Application\File;
/**
 * Prevents adding:
 *
 * use App\SomeClass;
 *
 * If there is already:
 *
 * SomeClass::callThis();
 */
final class FullyQualifiedNameClassNameImportSkipVoter implements ClassNameImportSkipVoterInterface
{
    /**
     * @readonly
     * @var \Rector\CodingStyle\ClassNameImport\ShortNameResolver
     */
    private $shortNameResolver;
    public function __construct(ShortNameResolver $shortNameResolver)
    {
        $this->shortNameResolver = $shortNameResolver;
    }
    public function shouldSkip(File $file, FullyQualifiedObjectType $fullyQualifiedObjectType, Node $node): bool
    {
        // "new X" or "X::static()"
        /** @var array<string, string> $shortNamesToFullyQualifiedNames */
        $shortNamesToFullyQualifiedNames = $this->shortNameResolver->resolveFromFile($file);
        $fullyQualifiedObjectTypeShortName = $fullyQualifiedObjectType->getShortName();
        $className = $fullyQualifiedObjectType->getClassName();
        foreach ($shortNamesToFullyQualifiedNames as $shortName => $fullyQualifiedName) {
            if ($fullyQualifiedObjectTypeShortName !== $shortName) {
                $shortName = $this->cleanShortName($shortName);
            }
            if ($fullyQualifiedObjectTypeShortName !== $shortName) {
                continue;
            }
            $fullyQualifiedName = ltrim($fullyQualifiedName, '\\');
            return $className !== $fullyQualifiedName;
        }
        return \false;
    }
    private function cleanShortName(string $shortName): string
    {
        return strncmp($shortName, '\\', strlen('\\')) === 0 ? ltrim((string) Strings::after($shortName, '\\', -1)) : $shortName;
    }
}
