<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Transform\Rector\Scalar;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr\ClassConstFetch;
use Argtyper202511\PhpParser\Node\Expr\ConstFetch;
use Argtyper202511\PhpParser\Node\Identifier;
use Argtyper202511\PhpParser\Node\Name\FullyQualified;
use Argtyper202511\PhpParser\Node\Scalar\Float_;
use Argtyper202511\PhpParser\Node\Scalar\Int_;
use Argtyper202511\PhpParser\Node\Scalar\String_;
use Argtyper202511\Rector\Contract\Rector\ConfigurableRectorInterface;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Rector\Transform\ValueObject\ScalarValueToConstFetch;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Argtyper202511\RectorPrefix202511\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\Transform\Rector\Scalar\ScalarValueToConstFetchRector\ScalarValueToConstFetchRectorTest
 */
final class ScalarValueToConstFetchRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var ScalarValueToConstFetch[]
     */
    private $scalarValueToConstFetches;
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Replaces Scalar values with a ConstFetch or ClassConstFetch', [new ConfiguredCodeSample(<<<'SAMPLE'
$var = 10;
SAMPLE
, <<<'SAMPLE'
$var = \SomeClass::FOOBAR_INT;
SAMPLE
, [new ScalarValueToConstFetch(new Int_(10), new ClassConstFetch(new FullyQualified('SomeClass'), new Identifier('FOOBAR_INT')))])]);
    }
    public function getNodeTypes(): array
    {
        return [String_::class, Float_::class, Int_::class];
    }
    /**
     * @param String_|Float_|Int_ $node
     * @return \PhpParser\Node\Expr\ConstFetch|\PhpParser\Node\Expr\ClassConstFetch|null
     */
    public function refactor(Node $node)
    {
        foreach ($this->scalarValueToConstFetches as $scalarValueToConstFetch) {
            if ($node->value === $scalarValueToConstFetch->getScalar()->value) {
                return $scalarValueToConstFetch->getConstFetch();
            }
        }
        return null;
    }
    public function configure(array $configuration): void
    {
        Assert::allIsAOf($configuration, ScalarValueToConstFetch::class);
        $this->scalarValueToConstFetches = $configuration;
    }
}
