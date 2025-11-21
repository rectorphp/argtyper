<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\DowngradePhp80\Rector\Property;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Identifier;
use Argtyper202511\PhpParser\Node\Stmt\Property;
use Argtyper202511\Rector\NodeManipulator\PropertyDecorator;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DowngradePhp80\Rector\Property\DowngradeMixedTypeTypedPropertyRector\DowngradeMixedTypeTypedPropertyRectorTest
 */
final class DowngradeMixedTypeTypedPropertyRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\NodeManipulator\PropertyDecorator
     */
    private $PropertyDecorator;
    public function __construct(PropertyDecorator $PropertyDecorator)
    {
        $this->PropertyDecorator = $PropertyDecorator;
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Property::class];
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Removes mixed type property type definition, adding `@var` annotations instead.', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    private mixed $property;
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    /**
     * @var mixed
     */
    private $property;
}
CODE_SAMPLE
)]);
    }
    /**
     * @param Property $node
     */
    public function refactor(Node $node): ?Node
    {
        if (!$node->type instanceof Identifier) {
            return null;
        }
        if ($node->type->toString() !== 'mixed') {
            return null;
        }
        $this->PropertyDecorator->decorateWithDocBlock($node, $node->type);
        $node->type = null;
        return $node;
    }
}
