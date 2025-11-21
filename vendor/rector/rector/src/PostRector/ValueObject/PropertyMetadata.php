<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\PostRector\ValueObject;

use Argtyper202511\PhpParser\Modifiers;
use Argtyper202511\PHPStan\Type\Type;
final class PropertyMetadata
{
    /**
     * @readonly
     * @var string
     */
    private $name;
    /**
     * @readonly
     * @var \PHPStan\Type\Type|null
     */
    private $type;
    /**
     * @readonly
     * @var int
     */
    private $flags = Modifiers::PRIVATE;
    public function __construct(string $name, ?Type $type, int $flags = Modifiers::PRIVATE)
    {
        $this->name = $name;
        $this->type = $type;
        $this->flags = $flags;
    }
    public function getName(): string
    {
        return $this->name;
    }
    public function getType(): ?Type
    {
        return $this->type;
    }
    public function getFlags(): int
    {
        return $this->flags;
    }
}
