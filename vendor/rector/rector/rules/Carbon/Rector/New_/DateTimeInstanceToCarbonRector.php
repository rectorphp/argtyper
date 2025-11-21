<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Carbon\Rector\New_;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr\MethodCall;
use Argtyper202511\PhpParser\Node\Expr\New_;
use Argtyper202511\PhpParser\Node\Expr\StaticCall;
use Argtyper202511\PhpParser\Node\Identifier;
use Argtyper202511\PhpParser\Node\Name\FullyQualified;
use Argtyper202511\PhpParser\Node\Scalar\String_;
use Argtyper202511\Rector\Carbon\NodeFactory\CarbonCallFactory;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Carbon\Rector\New_\DateTimeInstanceToCarbonRector\DateTimeInstanceToCarbonRectorTest
 */
final class DateTimeInstanceToCarbonRector extends AbstractRector
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
        return new RuleDefinition('Convert `new DateTime()` to `Carbon::*()`', [new CodeSample(<<<'CODE_SAMPLE'
$date = new \DateTime('today');
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$date = \Carbon\Carbon::today();
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
        if ($node->isFirstClassCallable()) {
            return null;
        }
        if ($this->isName($node->class, 'DateTime')) {
            return $this->refactorWithClass($node, 'Argtyper202511\\Carbon\\Carbon');
        }
        if ($this->isName($node->class, 'DateTimeImmutable')) {
            return $this->refactorWithClass($node, 'Argtyper202511\\Carbon\\CarbonImmutable');
        }
        return null;
    }
    /**
     * @return \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall|null
     */
    private function refactorWithClass(New_ $new, string $className)
    {
        // no arg? ::now()
        $carbonFullyQualified = new FullyQualified($className);
        if ($new->args === []) {
            return new StaticCall($carbonFullyQualified, new Identifier('now'));
        }
        if (\count($new->getArgs()) === 1) {
            $firstArg = $new->getArgs()[0];
            if ($firstArg->value instanceof String_) {
                return $this->carbonCallFactory->createFromDateTimeString($carbonFullyQualified, $firstArg->value);
            }
        }
        return null;
    }
}
