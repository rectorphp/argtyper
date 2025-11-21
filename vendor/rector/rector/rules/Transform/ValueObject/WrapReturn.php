<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Transform\ValueObject;

use Argtyper202511\PHPStan\Type\ObjectType;
use Argtyper202511\Rector\Validation\RectorAssert;
final class WrapReturn
{
    /**
     * @readonly
     * @var string
     */
    private $type;
    /**
     * @readonly
     * @var string
     */
    private $method;
    /**
     * @readonly
     * @var bool
     */
    private $isArrayWrap;
    public function __construct(string $type, string $method, bool $isArrayWrap)
    {
        $this->type = $type;
        $this->method = $method;
        $this->isArrayWrap = $isArrayWrap;
        RectorAssert::className($type);
    }
    public function getObjectType() : ObjectType
    {
        return new ObjectType($this->type);
    }
    public function getMethod() : string
    {
        return $this->method;
    }
    public function isArrayWrap() : bool
    {
        return $this->isArrayWrap;
    }
}
