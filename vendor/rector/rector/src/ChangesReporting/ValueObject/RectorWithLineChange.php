<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\ChangesReporting\ValueObject;

use Argtyper202511\Rector\Contract\Rector\RectorInterface;
use Argtyper202511\Rector\PostRector\Contract\Rector\PostRectorInterface;
use Argtyper202511\RectorPrefix202511\Symplify\EasyParallel\Contract\SerializableInterface;
use Argtyper202511\RectorPrefix202511\Webmozart\Assert\Assert;
final class RectorWithLineChange implements SerializableInterface
{
    /**
     * @var class-string<RectorInterface|PostRectorInterface>
     * @readonly
     */
    private $rectorClass;
    /**
     * @readonly
     * @var int
     */
    private $line;
    /**
     * @var string
     */
    private const KEY_RECTOR_CLASS = 'rector_class';
    /**
     * @var string
     */
    private const KEY_LINE = 'line';
    /**
     * @param class-string<RectorInterface|PostRectorInterface> $rectorClass
     */
    public function __construct(string $rectorClass, int $line)
    {
        $this->rectorClass = $rectorClass;
        $this->line = $line;
    }
    /**
     * @return class-string<RectorInterface|PostRectorInterface>
     */
    public function getRectorClass(): string
    {
        return $this->rectorClass;
    }
    /**
     * @param array<string, mixed> $json
     * @return $this
     */
    public static function decode(array $json): \Argtyper202511\RectorPrefix202511\Symplify\EasyParallel\Contract\SerializableInterface
    {
        /** @var class-string<RectorInterface> $rectorClass */
        $rectorClass = $json[self::KEY_RECTOR_CLASS];
        Assert::string($rectorClass);
        $line = $json[self::KEY_LINE];
        Assert::integer($line);
        return new self($rectorClass, $line);
    }
    /**
     * @return array{rector_class: class-string<RectorInterface|PostRectorInterface>, line: int}
     */
    public function jsonSerialize(): array
    {
        return [self::KEY_RECTOR_CLASS => $this->rectorClass, self::KEY_LINE => $this->line];
    }
}
