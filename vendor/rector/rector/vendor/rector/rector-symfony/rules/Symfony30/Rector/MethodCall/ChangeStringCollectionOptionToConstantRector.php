<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Symfony\Symfony30\Rector\MethodCall;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\ArrayItem;
use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Expr\Array_;
use Argtyper202511\PhpParser\Node\Expr\MethodCall;
use Argtyper202511\PhpParser\Node\Scalar\String_;
use Argtyper202511\Rector\PhpParser\Node\Value\ValueResolver;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Rector\Symfony\FormHelper\FormTypeStringToTypeProvider;
use Argtyper202511\Rector\Symfony\NodeAnalyzer\FormAddMethodCallAnalyzer;
use Argtyper202511\Rector\Symfony\NodeAnalyzer\FormCollectionAnalyzer;
use Argtyper202511\Rector\Symfony\NodeAnalyzer\FormOptionsArrayMatcher;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://symfony.com/doc/2.8/form/form_collections.html
 * @changelog https://symfony.com/doc/3.0/form/form_collections.html
 * @changelog https://symfony2-document.readthedocs.io/en/latest/reference/forms/types/collection.html#type
 *
 * @see \Rector\Symfony\Tests\Symfony30\Rector\MethodCall\ChangeStringCollectionOptionToConstantRector\ChangeStringCollectionOptionToConstantRectorTest
 */
final class ChangeStringCollectionOptionToConstantRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Symfony\NodeAnalyzer\FormAddMethodCallAnalyzer
     */
    private $formAddMethodCallAnalyzer;
    /**
     * @readonly
     * @var \Rector\Symfony\NodeAnalyzer\FormOptionsArrayMatcher
     */
    private $formOptionsArrayMatcher;
    /**
     * @readonly
     * @var \Rector\Symfony\FormHelper\FormTypeStringToTypeProvider
     */
    private $formTypeStringToTypeProvider;
    /**
     * @readonly
     * @var \Rector\Symfony\NodeAnalyzer\FormCollectionAnalyzer
     */
    private $formCollectionAnalyzer;
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\Value\ValueResolver
     */
    private $valueResolver;
    public function __construct(FormAddMethodCallAnalyzer $formAddMethodCallAnalyzer, FormOptionsArrayMatcher $formOptionsArrayMatcher, FormTypeStringToTypeProvider $formTypeStringToTypeProvider, FormCollectionAnalyzer $formCollectionAnalyzer, ValueResolver $valueResolver)
    {
        $this->formAddMethodCallAnalyzer = $formAddMethodCallAnalyzer;
        $this->formOptionsArrayMatcher = $formOptionsArrayMatcher;
        $this->formTypeStringToTypeProvider = $formTypeStringToTypeProvider;
        $this->formCollectionAnalyzer = $formCollectionAnalyzer;
        $this->valueResolver = $valueResolver;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change type in CollectionType from alias string to class reference', [new CodeSample(<<<'CODE_SAMPLE'
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class TaskType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('tags', CollectionType::class, [
            'type' => 'choice',
        ]);

        $builder->add('tags', 'collection', [
            'type' => 'choice',
        ]);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class TaskType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('tags', CollectionType::class, [
            'type' => \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class,
        ]);

        $builder->add('tags', 'collection', [
            'type' => \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class,
        ]);
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [MethodCall::class];
    }
    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (!$this->formAddMethodCallAnalyzer->isMatching($node)) {
            return null;
        }
        if (!$this->formCollectionAnalyzer->isCollectionType($node)) {
            return null;
        }
        $optionsArray = $this->formOptionsArrayMatcher->match($node);
        if (!$optionsArray instanceof Array_) {
            return null;
        }
        return $this->processChangeToConstant($optionsArray, $node);
    }
    private function processChangeToConstant(Array_ $optionsArray, MethodCall $methodCall): ?Node
    {
        foreach ($optionsArray->items as $optionsArrayItem) {
            if (!$optionsArrayItem instanceof ArrayItem) {
                continue;
            }
            if (!$optionsArrayItem->key instanceof Expr) {
                continue;
            }
            if (!$this->valueResolver->isValues($optionsArrayItem->key, ['type', 'entry_type'])) {
                continue;
            }
            // already ::class reference
            if (!$optionsArrayItem->value instanceof String_) {
                return null;
            }
            $stringValue = $optionsArrayItem->value->value;
            $formClass = $this->formTypeStringToTypeProvider->matchClassForNameWithPrefix($stringValue);
            if ($formClass === null) {
                return null;
            }
            $optionsArrayItem->value = $this->nodeFactory->createClassConstReference($formClass);
        }
        return $methodCall;
    }
}
