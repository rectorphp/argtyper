<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Visibility\ValueObject;

use Argtyper202511\PHPStan\Type\ObjectType;
use Argtyper202511\Rector\Validation\RectorAssert;
final class ChangeConstantVisibility
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
    private $constant;
    /**
     * @readonly
     * @var int
     */
    private $visibility;
    public function __construct(string $class, string $constant, int $visibility)
    {
        $this->class = $class;
        $this->constant = $constant;
        $this->visibility = $visibility;
        RectorAssert::className($class);
    }
    public function getObjectType(): ObjectType
    {
        return new ObjectType($this->class);
    }
    public function getConstant(): string
    {
        return $this->constant;
    }
    public function getVisibility(): int
    {
        return $this->visibility;
    }
}
