<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Naming\PropertyRenamer;

use Argtyper202511\PhpParser\Node\Stmt\Property;
use Argtyper202511\PhpParser\Node\VarLikeIdentifier;
use Argtyper202511\Rector\Naming\Guard\PropertyConflictingNameGuard\MatchPropertyTypeConflictingNameGuard;
use Argtyper202511\Rector\Naming\RenameGuard\PropertyRenameGuard;
use Argtyper202511\Rector\Naming\ValueObject\PropertyRename;
final class MatchTypePropertyRenamer
{
    /**
     * @readonly
     * @var \Rector\Naming\Guard\PropertyConflictingNameGuard\MatchPropertyTypeConflictingNameGuard
     */
    private $matchPropertyTypeConflictingNameGuard;
    /**
     * @readonly
     * @var \Rector\Naming\RenameGuard\PropertyRenameGuard
     */
    private $propertyRenameGuard;
    /**
     * @readonly
     * @var \Rector\Naming\PropertyRenamer\PropertyFetchRenamer
     */
    private $propertyFetchRenamer;
    public function __construct(MatchPropertyTypeConflictingNameGuard $matchPropertyTypeConflictingNameGuard, PropertyRenameGuard $propertyRenameGuard, \Argtyper202511\Rector\Naming\PropertyRenamer\PropertyFetchRenamer $propertyFetchRenamer)
    {
        $this->matchPropertyTypeConflictingNameGuard = $matchPropertyTypeConflictingNameGuard;
        $this->propertyRenameGuard = $propertyRenameGuard;
        $this->propertyFetchRenamer = $propertyFetchRenamer;
    }
    public function rename(PropertyRename $propertyRename) : ?Property
    {
        if ($this->matchPropertyTypeConflictingNameGuard->isConflicting($propertyRename)) {
            return null;
        }
        if ($propertyRename->isAlreadyExpectedName()) {
            return null;
        }
        if ($this->propertyRenameGuard->shouldSkip($propertyRename)) {
            return null;
        }
        $propertyItem = $propertyRename->getPropertyProperty();
        $propertyItem->name = new VarLikeIdentifier($propertyRename->getExpectedName());
        $this->renamePropertyFetchesInClass($propertyRename);
        return $propertyRename->getProperty();
    }
    private function renamePropertyFetchesInClass(PropertyRename $propertyRename) : void
    {
        $this->propertyFetchRenamer->renamePropertyFetchesInClass($propertyRename->getClassLike(), $propertyRename->getCurrentName(), $propertyRename->getExpectedName());
    }
}
