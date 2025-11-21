<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\Reflection\BetterReflection\SourceLocator;

use Argtyper202511\PHPStan\BetterReflection\Identifier\Identifier;
use Argtyper202511\PHPStan\BetterReflection\Identifier\IdentifierType;
use Argtyper202511\PHPStan\BetterReflection\Reflection\Reflection;
use Argtyper202511\PHPStan\BetterReflection\Reflector\Reflector;
use Argtyper202511\PHPStan\BetterReflection\SourceLocator\Type\SourceLocator;
use Argtyper202511\PHPStan\File\CouldNotReadFileException;
use Rector\NodeTypeResolver\Reflection\BetterReflection\SourceLocatorProvider\DynamicSourceLocatorProvider;
final class IntermediateSourceLocator implements SourceLocator
{
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\Reflection\BetterReflection\SourceLocatorProvider\DynamicSourceLocatorProvider
     */
    private $dynamicSourceLocatorProvider;
    public function __construct(DynamicSourceLocatorProvider $dynamicSourceLocatorProvider)
    {
        $this->dynamicSourceLocatorProvider = $dynamicSourceLocatorProvider;
    }
    public function locateIdentifier(Reflector $reflector, Identifier $identifier): ?Reflection
    {
        $sourceLocator = $this->dynamicSourceLocatorProvider->provide();
        try {
            $reflection = $sourceLocator->locateIdentifier($reflector, $identifier);
        } catch (CouldNotReadFileException $exception) {
            return null;
        }
        if ($reflection instanceof Reflection) {
            return $reflection;
        }
        return null;
    }
    /**
     * Find all identifiers of a type
     * @return array<int, Reflection>
     */
    public function locateIdentifiersByType(Reflector $reflector, IdentifierType $identifierType): array
    {
        $sourceLocator = $this->dynamicSourceLocatorProvider->provide();
        try {
            $reflections = $sourceLocator->locateIdentifiersByType($reflector, $identifierType);
        } catch (CouldNotReadFileException $exception) {
            return [];
        }
        if ($reflections !== []) {
            return $reflections;
        }
        return [];
    }
}
