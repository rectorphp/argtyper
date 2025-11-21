<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Symfony\Symfony30\Rector\MethodCall;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\ArrayItem;
use Argtyper202511\PhpParser\Node\Expr\Array_;
use Argtyper202511\PhpParser\Node\Expr\MethodCall;
use Argtyper202511\PhpParser\Node\Scalar\String_;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Rector\Symfony\NodeAnalyzer\FormAddMethodCallAnalyzer;
use Argtyper202511\Rector\Symfony\NodeAnalyzer\FormOptionsArrayMatcher;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Symfony\Tests\Symfony30\Rector\MethodCall\OptionNameRector\OptionNameRectorTest
 */
final class OptionNameRector extends AbstractRector
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
     * @var array<string, string>
     */
    private const OLD_TO_NEW_OPTION = ['precision' => 'scale', 'virtual' => 'inherit_data'];
    public function __construct(FormAddMethodCallAnalyzer $formAddMethodCallAnalyzer, FormOptionsArrayMatcher $formOptionsArrayMatcher)
    {
        $this->formAddMethodCallAnalyzer = $formAddMethodCallAnalyzer;
        $this->formOptionsArrayMatcher = $formOptionsArrayMatcher;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Turns old option names to new ones in FormTypes in Form in Symfony', [new CodeSample(<<<'CODE_SAMPLE'
use Symfony\Component\Form\FormBuilder;

$formBuilder = new FormBuilder;
$formBuilder->add("...", ["precision" => "...", "virtual" => "..."];
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Component\Form\FormBuilder;

$formBuilder = new FormBuilder;
$formBuilder->add("...", ["scale" => "...", "inherit_data" => "..."];
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [MethodCall::class];
    }
    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->formAddMethodCallAnalyzer->isMatching($node)) {
            return null;
        }
        $optionsArray = $this->formOptionsArrayMatcher->match($node);
        if (!$optionsArray instanceof Array_) {
            return null;
        }
        foreach ($optionsArray->items as $arrayItemNode) {
            if (!$arrayItemNode instanceof ArrayItem) {
                continue;
            }
            if (!$arrayItemNode->key instanceof String_) {
                continue;
            }
            $this->processStringKey($arrayItemNode->key);
        }
        return $node;
    }
    private function processStringKey(String_ $string) : void
    {
        $currentOptionName = $string->value;
        $string->value = self::OLD_TO_NEW_OPTION[$currentOptionName] ?? $string->value;
    }
}
