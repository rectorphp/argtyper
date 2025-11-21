<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Doctrine\TypedCollections\Rector\MethodCall;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Arg;
use Argtyper202511\PhpParser\Node\Expr\Array_;
use Argtyper202511\PhpParser\Node\Expr\MethodCall;
use Argtyper202511\PhpParser\Node\Expr\New_;
use Argtyper202511\PhpParser\Node\Expr\StaticCall;
use Argtyper202511\PhpParser\Node\Name\FullyQualified;
use Argtyper202511\PHPStan\Type\ObjectType;
use Argtyper202511\Rector\Doctrine\Enum\DoctrineClass;
use Argtyper202511\Rector\Doctrine\TypedCollections\NodeAnalyzer\CollectionParamCallDetector;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Doctrine\Tests\TypedCollections\Rector\MethodCall\SetArrayToNewCollectionRector\SetArrayToNewCollectionRectorTest
 */
final class SetArrayToNewCollectionRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Doctrine\TypedCollections\NodeAnalyzer\CollectionParamCallDetector
     */
    private $collectionParamCallDetector;
    public function __construct(CollectionParamCallDetector $collectionParamCallDetector)
    {
        $this->collectionParamCallDetector = $collectionParamCallDetector;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change array to new ArrayCollection() on collection typed property', [new CodeSample(<<<'CODE_SAMPLE'
use Doctrine\Common\Collections\ArrayCollection;

final class SomeClass
{
    /**
     * @var ArrayCollection<int, string>
     */
    public $items;

    public function someMethod()
    {
        $this->items = [];
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

final class SomeClass
{
    /**
     * @var Collection<int, string>
     */
    public $items;

    public function someMethod()
    {
        $this->items = new ArrayCollection([]);
    }
}
CODE_SAMPLE
)]);
    }
    public function getNodeTypes() : array
    {
        return [MethodCall::class, New_::class, StaticCall::class];
    }
    /**
     * @param MethodCall|New_|StaticCall $node
     * @return \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\New_|\PhpParser\Node\Expr\StaticCall|null
     */
    public function refactor(Node $node)
    {
        if ($node->isFirstClassCallable()) {
            return null;
        }
        $hasChanged = \false;
        foreach ($node->getArgs() as $position => $arg) {
            $soleArgType = $this->getType($arg->value);
            if ($soleArgType instanceof ObjectType) {
                continue;
            }
            if (!$this->collectionParamCallDetector->detect($node, $position)) {
                continue;
            }
            $oldArgValue = $arg->value;
            // wrap argument with a collection instance
            $defaultExpr = $this->isName($oldArgValue, 'null') ? new Array_() : $oldArgValue;
            $arg->value = new New_(new FullyQualified(DoctrineClass::ARRAY_COLLECTION), [new Arg($defaultExpr)]);
            $hasChanged = \true;
        }
        if (!$hasChanged) {
            return null;
        }
        return $node;
    }
}
