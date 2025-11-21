<?php

declare (strict_types=1);
namespace Rector\StaticTypeMapper\ValueObject\Type;

use Argtyper202511\PHPStan\Type\StaticType;
final class SimpleStaticType extends StaticType
{
    /**
     * @readonly
     * @var string
     */
    private $className;
    public function __construct(string $className)
    {
        $this->className = $className;
    }
    public function getClassName(): string
    {
        return $this->className;
    }
}
