<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Transform\ValueObject;

use Argtyper202511\Rector\Validation\RectorAssert;
final class StaticCallToNew
{
    /**
     * @readonly
     * @var string
     */
    private $class;
    /**
     * @readonly
     * @var string
     */
    private $method;
    public function __construct(string $class, string $method)
    {
        $this->class = $class;
        $this->method = $method;
        RectorAssert::className($class);
        RectorAssert::methodName($method);
    }
    public function getClass() : string
    {
        return $this->class;
    }
    public function getMethod() : string
    {
        return $this->method;
    }
}
