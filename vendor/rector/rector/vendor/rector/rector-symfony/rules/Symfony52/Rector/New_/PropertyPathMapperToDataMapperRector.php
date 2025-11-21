<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Symfony\Symfony52\Rector\New_;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr\New_;
use Argtyper202511\PhpParser\Node\Name;
use Argtyper202511\PhpParser\Node\Name\FullyQualified;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://github.com/symfony/symfony/blob/5.x/UPGRADE-5.2.md#form
 * @see \Rector\Symfony\Tests\Symfony52\Rector\New_\PropertyPathMapperToDataMapperRector\PropertyPathMapperToDataMapperRectorTest
 */
final class PropertyPathMapperToDataMapperRector extends AbstractRector
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Migrate from PropertyPathMapper to DataMapper and PropertyPathAccessor', [new CodeSample(<<<'CODE_SAMPLE'
use Symfony\Component\Form\Extension\Core\DataMapper\PropertyPathMapper;

class SomeClass
{
    public function run()
    {
        return new PropertyPathMapper();
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Component\Form\Extension\Core\DataMapper\PropertyPathMapper;

class SomeClass
{
    public function run()
    {
        return new \Symfony\Component\Form\Extension\Core\DataMapper\DataMapper(new \Symfony\Component\Form\Extension\Core\DataAccessor\PropertyPathAccessor());
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [New_::class];
    }
    /**
     * @param New_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }
        return $this->generateNewInstances($node);
    }
    private function shouldSkip(New_ $new) : bool
    {
        if (!$new->class instanceof Name) {
            return \true;
        }
        return !$this->isName($new->class, 'Argtyper202511\\Symfony\\Component\\Form\\Extension\\Core\\DataMapper\\PropertyPathMapper');
    }
    private function generateNewInstances(New_ $new) : New_
    {
        $arguments = [];
        if (isset($new->args[0])) {
            $arguments = [$new->args[0]];
        }
        $new = new New_(new FullyQualified('Argtyper202511\\Symfony\\Component\\Form\\Extension\\Core\\DataAccessor\\PropertyPathAccessor'), $arguments);
        return new New_(new FullyQualified('Argtyper202511\\Symfony\\Component\\Form\\Extension\\Core\\DataMapper\\DataMapper'), [$this->nodeFactory->createArg($new)]);
    }
}
