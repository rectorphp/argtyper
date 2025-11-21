<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Naming\Guard\PropertyConflictingNameGuard;

use Argtyper202511\PhpParser\Node\Stmt\ClassLike;
use Argtyper202511\Rector\Naming\ExpectedNameResolver\MatchPropertyTypeExpectedNameResolver;
use Argtyper202511\Rector\Naming\PhpArray\ArrayFilter;
use Argtyper202511\Rector\Naming\ValueObject\PropertyRename;
use Argtyper202511\Rector\NodeNameResolver\NodeNameResolver;
final class MatchPropertyTypeConflictingNameGuard
{
    /**
     * @readonly
     * @var \Rector\Naming\ExpectedNameResolver\MatchPropertyTypeExpectedNameResolver
     */
    private $matchPropertyTypeExpectedNameResolver;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\Naming\PhpArray\ArrayFilter
     */
    private $arrayFilter;
    public function __construct(MatchPropertyTypeExpectedNameResolver $matchPropertyTypeExpectedNameResolver, NodeNameResolver $nodeNameResolver, ArrayFilter $arrayFilter)
    {
        $this->matchPropertyTypeExpectedNameResolver = $matchPropertyTypeExpectedNameResolver;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->arrayFilter = $arrayFilter;
    }
    public function isConflicting(PropertyRename $propertyRename): bool
    {
        $conflictingPropertyNames = $this->resolve($propertyRename->getClassLike());
        return in_array($propertyRename->getExpectedName(), $conflictingPropertyNames, \true);
    }
    /**
     * @return string[]
     */
    private function resolve(ClassLike $classLike): array
    {
        $expectedNames = [];
        foreach ($classLike->getProperties() as $property) {
            $expectedName = $this->matchPropertyTypeExpectedNameResolver->resolve($property, $classLike);
            if ($expectedName === null) {
                // fallback to existing name
                $expectedName = $this->nodeNameResolver->getName($property);
            }
            $expectedNames[] = $expectedName;
        }
        return $this->arrayFilter->filterWithAtLeastTwoOccurrences($expectedNames);
    }
}
