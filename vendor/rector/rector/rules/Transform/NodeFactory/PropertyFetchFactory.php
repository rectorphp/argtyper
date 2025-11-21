<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Transform\NodeFactory;

use Argtyper202511\PhpParser\Node\Expr\PropertyFetch;
use Argtyper202511\PhpParser\Node\Expr\Variable;
use Argtyper202511\PHPStan\Type\ObjectType;
use Argtyper202511\Rector\Naming\Naming\PropertyNaming;
final class PropertyFetchFactory
{
    /**
     * @readonly
     * @var \Rector\Naming\Naming\PropertyNaming
     */
    private $propertyNaming;
    public function __construct(PropertyNaming $propertyNaming)
    {
        $this->propertyNaming = $propertyNaming;
    }
    public function createFromType(ObjectType $objectType) : PropertyFetch
    {
        $thisVariable = new Variable('this');
        $propertyName = $this->propertyNaming->fqnToVariableName($objectType->getClassName());
        return new PropertyFetch($thisVariable, $propertyName);
    }
}
