<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\CodingStyle\ClassNameImport\ClassNameImportSkipVoter;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\Rector\CodingStyle\ClassNameImport\AliasUsesResolver;
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
 * use App\Something as SomeClass;
 */
final class AliasClassNameImportSkipVoter implements ClassNameImportSkipVoterInterface
{
    /**
     * @readonly
     * @var \Rector\CodingStyle\ClassNameImport\AliasUsesResolver
     */
    private $aliasUsesResolver;
    public function __construct(AliasUsesResolver $aliasUsesResolver)
    {
        $this->aliasUsesResolver = $aliasUsesResolver;
    }
    public function shouldSkip(File $file, FullyQualifiedObjectType $fullyQualifiedObjectType, Node $node): bool
    {
        $aliasedUses = $this->aliasUsesResolver->resolveFromNode($node, $file->getNewStmts());
        $longNameLowered = strtolower($fullyQualifiedObjectType->getClassName());
        $shortNameLowered = $fullyQualifiedObjectType->getShortNameLowered();
        foreach ($aliasedUses as $aliasedUse) {
            $aliasedUseLowered = strtolower($aliasedUse);
            // its aliased, we cannot just rename it
            if (substr_compare($aliasedUseLowered, '\\' . $shortNameLowered, -strlen('\\' . $shortNameLowered)) === 0) {
                return \true;
            }
            if ($aliasedUseLowered === $shortNameLowered && $longNameLowered === $shortNameLowered) {
                return \true;
            }
        }
        return \false;
    }
}
