<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Transform\ValueObject;

use Argtyper202511\Rector\Validation\RectorAssert;
use Argtyper202511\RectorPrefix202511\Webmozart\Assert\Assert;
final class ParentClassToTraits
{
    /**
     * @readonly
     * @var string
     */
    private $parentType;
    /**
     * @var string[]
     * @readonly
     */
    private $traitNames;
    /**
     * @param string[] $traitNames
     */
    public function __construct(string $parentType, array $traitNames)
    {
        $this->parentType = $parentType;
        $this->traitNames = $traitNames;
        RectorAssert::className($parentType);
        Assert::allString($traitNames);
    }
    public function getParentType(): string
    {
        return $this->parentType;
    }
    /**
     * @return string[]
     */
    public function getTraitNames(): array
    {
        // keep the Trait order the way it is in config
        return array_reverse($this->traitNames);
    }
}
