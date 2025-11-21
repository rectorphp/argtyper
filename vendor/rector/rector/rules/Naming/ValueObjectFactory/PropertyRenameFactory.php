<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Naming\ValueObjectFactory;

use Argtyper202511\PhpParser\Node\Stmt\Class_;
use Argtyper202511\PhpParser\Node\Stmt\Property;
use Argtyper202511\Rector\Naming\ValueObject\PropertyRename;
use Argtyper202511\Rector\NodeNameResolver\NodeNameResolver;
use Argtyper202511\RectorPrefix202511\Webmozart\Assert\InvalidArgumentException;
final class PropertyRenameFactory
{
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }
    public function createFromExpectedName(Class_ $class, Property $property, string $expectedName) : ?PropertyRename
    {
        $currentName = $this->nodeNameResolver->getName($property);
        $className = (string) $this->nodeNameResolver->getName($class);
        try {
            return new PropertyRename($property, $expectedName, $currentName, $class, $className, $property->props[0]);
        } catch (InvalidArgumentException $exception) {
        }
        return null;
    }
}
