<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\CodingStyle\ClassNameImport;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Identifier;
use Argtyper202511\PhpParser\Node\Name\FullyQualified;
use Argtyper202511\PhpParser\Node\Stmt\GroupUse;
use Argtyper202511\PhpParser\Node\Stmt\Use_;
use Argtyper202511\PhpParser\Node\UseItem;
use Argtyper202511\Rector\CodingStyle\Contract\ClassNameImport\ClassNameImportSkipVoterInterface;
use Argtyper202511\Rector\Naming\Naming\UseImportsResolver;
use Argtyper202511\Rector\NodeTypeResolver\Node\AttributeKey;
use Argtyper202511\Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
use Argtyper202511\Rector\ValueObject\Application\File;
final class ClassNameImportSkipper
{
    /**
     * @var ClassNameImportSkipVoterInterface[]
     * @readonly
     */
    private $classNameImportSkipVoters;
    /**
     * @readonly
     * @var \Rector\Naming\Naming\UseImportsResolver
     */
    private $useImportsResolver;
    /**
     * @param ClassNameImportSkipVoterInterface[] $classNameImportSkipVoters
     */
    public function __construct(iterable $classNameImportSkipVoters, UseImportsResolver $useImportsResolver)
    {
        $this->classNameImportSkipVoters = $classNameImportSkipVoters;
        $this->useImportsResolver = $useImportsResolver;
    }
    public function shouldSkipNameForFullyQualifiedObjectType(File $file, Node $node, FullyQualifiedObjectType $fullyQualifiedObjectType): bool
    {
        foreach ($this->classNameImportSkipVoters as $classNameImportSkipVoter) {
            if ($classNameImportSkipVoter->shouldSkip($file, $fullyQualifiedObjectType, $node)) {
                return \true;
            }
        }
        return \false;
    }
    /**
     * @param array<Use_|GroupUse> $uses
     */
    public function shouldSkipName(FullyQualified $fullyQualified, array $uses): bool
    {
        if (substr_count($fullyQualified->toCodeString(), '\\') === 1) {
            return $this->isFunctionOrConstantImport($fullyQualified);
        }
        $stringName = $fullyQualified->toString();
        $lastUseName = $fullyQualified->getLast();
        $nameLastName = strtolower($lastUseName);
        foreach ($uses as $use) {
            $prefix = $this->useImportsResolver->resolvePrefix($use);
            $useName = $prefix . $stringName;
            foreach ($use->uses as $useUse) {
                $useUseLastName = strtolower($useUse->name->getLast());
                if ($useUseLastName !== $nameLastName) {
                    continue;
                }
                if ($this->isConflictedShortNameInUse($useUse, $useName, $lastUseName, $stringName)) {
                    return \true;
                }
                return $prefix . $useUse->name->toString() !== $stringName;
            }
        }
        return \false;
    }
    private function isFunctionOrConstantImport(FullyQualified $fullyQualified): bool
    {
        if ($fullyQualified->getAttribute(AttributeKey::IS_CONSTFETCH_NAME) === \true) {
            return \true;
        }
        return $fullyQualified->getAttribute(AttributeKey::IS_FUNCCALL_NAME) === \true;
    }
    private function isConflictedShortNameInUse(UseItem $useItem, string $useName, string $lastUseName, string $stringName): bool
    {
        if (!$useItem->alias instanceof Identifier && $useName !== $stringName && $lastUseName === $stringName) {
            return \true;
        }
        return $useItem->alias instanceof Identifier && $useItem->alias->toString() === $stringName;
    }
}
