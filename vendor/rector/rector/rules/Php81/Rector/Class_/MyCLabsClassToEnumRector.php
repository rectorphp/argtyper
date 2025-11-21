<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Php81\Rector\Class_;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Stmt\Class_;
use Argtyper202511\PHPStan\Type\ObjectType;
use Argtyper202511\Rector\Php81\NodeFactory\EnumFactory;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Rector\ValueObject\PhpVersionFeature;
use Argtyper202511\Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Php81\Rector\Class_\MyCLabsClassToEnumRector\MyCLabsClassToEnumRectorTest
 */
final class MyCLabsClassToEnumRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \Rector\Php81\NodeFactory\EnumFactory
     */
    private $enumFactory;
    public function __construct(EnumFactory $enumFactory)
    {
        $this->enumFactory = $enumFactory;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Refactor MyCLabs enum class to native Enum', [new CodeSample(<<<'CODE_SAMPLE'
use MyCLabs\Enum\Enum;

final class Action extends Enum
{
    private const VIEW = 'view';
    private const EDIT = 'edit';
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
enum Action : string
{
    case VIEW = 'view';
    case EDIT = 'edit';
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->isObjectType($node, new ObjectType('Argtyper202511\\MyCLabs\\Enum\\Enum'))) {
            return null;
        }
        return $this->enumFactory->createFromClass($node);
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::ENUM;
    }
}
