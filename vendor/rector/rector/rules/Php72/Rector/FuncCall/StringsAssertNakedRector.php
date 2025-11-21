<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Php72\Rector\FuncCall;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Arg;
use Argtyper202511\PhpParser\Node\Expr\FuncCall;
use Argtyper202511\PhpParser\Node\Scalar\String_;
use Argtyper202511\PhpParser\Node\Stmt\Expression;
use Argtyper202511\Rector\PhpParser\Parser\SimplePhpParser;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Rector\ValueObject\PhpVersionFeature;
use Argtyper202511\Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Php72\Rector\FuncCall\StringsAssertNakedRector\StringsAssertNakedRectorTest
 */
final class StringsAssertNakedRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \Rector\PhpParser\Parser\SimplePhpParser
     */
    private $simplePhpParser;
    public function __construct(SimplePhpParser $simplePhpParser)
    {
        $this->simplePhpParser = $simplePhpParser;
    }
    public function provideMinPhpVersion(): int
    {
        return PhpVersionFeature::STRING_IN_ASSERT_ARG;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('String asserts must be passed directly to assert()', [new CodeSample(<<<'CODE_SAMPLE'
function nakedAssert()
{
    assert('true === true');
    assert("true === true");
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
function nakedAssert()
{
    assert(true === true);
    assert(true === true);
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [FuncCall::class];
    }
    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (!$this->isName($node, 'assert')) {
            return null;
        }
        if ($node->isFirstClassCallable()) {
            return null;
        }
        $firstArg = $node->getArgs()[0];
        $firstArgValue = $firstArg->value;
        if (!$firstArgValue instanceof String_) {
            return null;
        }
        $phpCode = '<?php ' . $firstArgValue->value . ';';
        $contentStmts = $this->simplePhpParser->parseString($phpCode);
        if (!isset($contentStmts[0])) {
            return null;
        }
        if (!$contentStmts[0] instanceof Expression) {
            return null;
        }
        $node->args[0] = new Arg($contentStmts[0]->expr);
        return $node;
    }
}
