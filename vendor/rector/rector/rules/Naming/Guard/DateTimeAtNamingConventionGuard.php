<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Naming\Guard;

use DateTimeInterface;
use Argtyper202511\Rector\Naming\ValueObject\PropertyRename;
use Argtyper202511\Rector\NodeTypeResolver\NodeTypeResolver;
use Argtyper202511\Rector\PHPStanStaticTypeMapper\Utils\TypeUnwrapper;
use Argtyper202511\Rector\StaticTypeMapper\Resolver\ClassNameFromObjectTypeResolver;
use Argtyper202511\Rector\Util\StringUtils;
final class DateTimeAtNamingConventionGuard
{
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    /**
     * @readonly
     * @var \Rector\PHPStanStaticTypeMapper\Utils\TypeUnwrapper
     */
    private $typeUnwrapper;
    public function __construct(NodeTypeResolver $nodeTypeResolver, TypeUnwrapper $typeUnwrapper)
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->typeUnwrapper = $typeUnwrapper;
    }
    public function isConflicting(PropertyRename $propertyRename) : bool
    {
        $type = $this->nodeTypeResolver->getType($propertyRename->getProperty());
        $type = $this->typeUnwrapper->unwrapFirstObjectTypeFromUnionType($type);
        $className = ClassNameFromObjectTypeResolver::resolve($type);
        if ($className === null) {
            return \false;
        }
        if (!\is_a($className, DateTimeInterface::class, \true)) {
            return \false;
        }
        return StringUtils::isMatch($propertyRename->getCurrentName(), \Argtyper202511\Rector\Naming\Guard\BreakingVariableRenameGuard::AT_NAMING_REGEX);
    }
}
