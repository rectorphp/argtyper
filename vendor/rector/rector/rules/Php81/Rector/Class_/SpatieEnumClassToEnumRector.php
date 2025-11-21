<?php

declare (strict_types=1);
namespace Rector\Php81\Rector\Class_;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Stmt\Class_;
use Argtyper202511\PhpParser\Node\Stmt\Enum_;
use Argtyper202511\PHPStan\Type\ObjectType;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\Php81\NodeFactory\EnumFactory;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Php81\Rector\Class_\SpatieEnumClassToEnumRector\SpatieEnumClassToEnumRectorTest
 */
final class SpatieEnumClassToEnumRector extends AbstractRector implements MinPhpVersionInterface, ConfigurableRectorInterface
{
    /**
     * @readonly
     * @var \Rector\Php81\NodeFactory\EnumFactory
     */
    private $enumFactory;
    /**
     * @var string
     */
    public const TO_UPPER_SNAKE_CASE = 'toUpperSnakeCase';
    /**
     * @var bool
     */
    private $toUpperSnakeCase = \false;
    public function __construct(EnumFactory $enumFactory)
    {
        $this->enumFactory = $enumFactory;
    }
    public function provideMinPhpVersion(): int
    {
        return PhpVersionFeature::ENUM;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Refactor Spatie enum class to native Enum', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
use \Spatie\Enum\Enum;

/**
 * @method static self draft()
 * @method static self published()
 * @method static self archived()
 */
class StatusEnum extends Enum
{
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
enum StatusEnum : string
{
    case DRAFT = 'draft';
    case PUBLISHED = 'published';
    case ARCHIVED = 'archived';
}
CODE_SAMPLE
, [self::TO_UPPER_SNAKE_CASE => \false])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Enum_
    {
        if (!$this->isObjectType($node, new ObjectType('Argtyper202511\Spatie\Enum\Enum'))) {
            return null;
        }
        return $this->enumFactory->createFromSpatieClass($node, $this->toUpperSnakeCase);
    }
    /**
     * @param array<string, mixed> $configuration
     */
    public function configure(array $configuration): void
    {
        $this->toUpperSnakeCase = $configuration[self::TO_UPPER_SNAKE_CASE] ?? \false;
    }
}
