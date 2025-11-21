<?php

declare (strict_types=1);
namespace Rector\Symfony\Symfony73\ValueObject;

use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PHPStan\Type\Type;
final class CommandArgument
{
    /**
     * @readonly
     * @var string
     */
    private $nameValue;
    /**
     * @readonly
     * @var \PhpParser\Node\Expr
     */
    private $name;
    /**
     * @readonly
     * @var \PhpParser\Node\Expr|null
     */
    private $mode;
    /**
     * @readonly
     * @var \PhpParser\Node\Expr|null
     */
    private $description;
    /**
     * @readonly
     * @var \PhpParser\Node\Expr|null
     */
    private $default;
    /**
     * @readonly
     * @var bool
     */
    private $isArray;
    /**
     * @readonly
     * @var \PHPStan\Type\Type|null
     */
    private $defaultType;
    public function __construct(string $nameValue, Expr $name, ?Expr $mode, ?Expr $description, ?Expr $default, bool $isArray, ?Type $defaultType)
    {
        $this->nameValue = $nameValue;
        $this->name = $name;
        $this->mode = $mode;
        $this->description = $description;
        $this->default = $default;
        $this->isArray = $isArray;
        $this->defaultType = $defaultType;
    }
    public function getNameValue(): string
    {
        return $this->nameValue;
    }
    public function getName(): Expr
    {
        return $this->name;
    }
    public function getMode(): ?Expr
    {
        return $this->mode;
    }
    public function getDescription(): ?Expr
    {
        return $this->description;
    }
    public function getDefault(): ?Expr
    {
        return $this->default;
    }
    public function isArray(): bool
    {
        return $this->isArray;
    }
    public function getDefaultType(): ?Type
    {
        return $this->defaultType;
    }
}
