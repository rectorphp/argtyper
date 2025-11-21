<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Carbon\Rector\MethodCall;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr\MethodCall;
use Argtyper202511\PhpParser\Node\Expr\New_;
use Argtyper202511\PhpParser\Node\Name;
use Argtyper202511\PhpParser\Node\Name\FullyQualified;
use Argtyper202511\PhpParser\Node\Scalar\String_;
use Argtyper202511\Rector\Carbon\NodeFactory\CarbonCallFactory;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Carbon\Rector\MethodCall\DateTimeMethodCallToCarbonRector\DateTimeMethodCallToCarbonRectorTest
 */
final class DateTimeMethodCallToCarbonRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Carbon\NodeFactory\CarbonCallFactory
     */
    private $carbonCallFactory;
    public function __construct(CarbonCallFactory $carbonCallFactory)
    {
        $this->carbonCallFactory = $carbonCallFactory;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Convert `new DateTime()` with a method call to `Carbon::*()`', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $date = (new \DateTime('today +20 day'))->format('Y-m-d');
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $date = \Carbon\Carbon::today()->addDays(20)->format('Y-m-d')
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
        return [MethodCall::class];
    }
    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$node->var instanceof New_) {
            return null;
        }
        $new = $node->var;
        if (!$new->class instanceof Name) {
            return null;
        }
        if (!$this->isName($new->class, 'DateTime') && !$this->isName($new->class, 'DateTimeImmutable')) {
            return null;
        }
        if ($new->isFirstClassCallable()) {
            return null;
        }
        if (\count($new->getArgs()) !== 1) {
            // @todo handle in separate static call
            return null;
        }
        $firstArg = $new->getArgs()[0];
        if (!$firstArg->value instanceof String_) {
            return null;
        }
        if ($this->isName($new->class, 'DateTime')) {
            $carbonFullyQualified = new FullyQualified('Argtyper202511\\Carbon\\Carbon');
        } else {
            $carbonFullyQualified = new FullyQualified('Argtyper202511\\Carbon\\CarbonImmutable');
        }
        $carbonCall = $this->carbonCallFactory->createFromDateTimeString($carbonFullyQualified, $firstArg->value);
        $node->var = $carbonCall;
        return $node;
    }
}
