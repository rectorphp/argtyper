<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Naming\ExpectedNameResolver;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Param;
use Argtyper202511\Rector\Naming\Naming\PropertyNaming;
use Argtyper202511\Rector\Naming\ValueObject\ExpectedName;
use Argtyper202511\Rector\StaticTypeMapper\StaticTypeMapper;
final class MatchParamTypeExpectedNameResolver
{
    /**
     * @readonly
     * @var \Rector\StaticTypeMapper\StaticTypeMapper
     */
    private $staticTypeMapper;
    /**
     * @readonly
     * @var \Rector\Naming\Naming\PropertyNaming
     */
    private $propertyNaming;
    public function __construct(StaticTypeMapper $staticTypeMapper, PropertyNaming $propertyNaming)
    {
        $this->staticTypeMapper = $staticTypeMapper;
        $this->propertyNaming = $propertyNaming;
    }
    public function resolve(Param $param): ?string
    {
        // nothing to verify
        if (!$param->type instanceof Node) {
            return null;
        }
        $staticType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($param->type);
        $expectedName = $this->propertyNaming->getExpectedNameFromType($staticType);
        if (!$expectedName instanceof ExpectedName) {
            return null;
        }
        return $expectedName->getName();
    }
}
