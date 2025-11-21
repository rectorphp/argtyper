<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\CodeQuality\Rector\NullsafeMethodCall;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr\FuncCall;
use Argtyper202511\PhpParser\Node\Expr\MethodCall;
use Argtyper202511\PhpParser\Node\Expr\NullsafeMethodCall;
use Argtyper202511\PhpParser\Node\Expr\StaticCall;
use Argtyper202511\PhpParser\Node\Identifier;
use Argtyper202511\PHPStan\Type\ObjectType;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Rector\TypeDeclaration\TypeAnalyzer\ReturnStrictTypeAnalyzer;
use Argtyper202511\Rector\ValueObject\PhpVersionFeature;
use Argtyper202511\Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://wiki.php.net/rfc/nullsafe_operator
 *
 * @see \Rector\Tests\CodeQuality\Rector\NullsafeMethodCall\CleanupUnneededNullsafeOperatorRector\CleanupUnneededNullsafeOperatorRectorTest
 */
final class CleanupUnneededNullsafeOperatorRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\TypeAnalyzer\ReturnStrictTypeAnalyzer
     */
    private $returnStrictTypeAnalyzer;
    public function __construct(ReturnStrictTypeAnalyzer $returnStrictTypeAnalyzer)
    {
        $this->returnStrictTypeAnalyzer = $returnStrictTypeAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Cleanup unneeded nullsafe operator', [new CodeSample(<<<'CODE_SAMPLE'
class HelloWorld {
    public function getString(): string
    {
         return 'hello world';
    }
}

function get(): HelloWorld
{
     return new HelloWorld();
}

echo get()?->getString();
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class HelloWorld {
    public function getString(): string
    {
         return 'hello world';
    }
}

function get(): HelloWorld
{
     return new HelloWorld();
}

echo get()->getString();
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [NullsafeMethodCall::class];
    }
    /**
     * @param NullsafeMethodCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$node->name instanceof Identifier) {
            return null;
        }
        if (!$node->var instanceof FuncCall && !$node->var instanceof MethodCall && !$node->var instanceof StaticCall) {
            return null;
        }
        $returnType = $this->returnStrictTypeAnalyzer->resolveMethodCallReturnType($node->var);
        if (!$returnType instanceof ObjectType) {
            return null;
        }
        return new MethodCall($node->var, $node->name, $node->args);
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::NULLSAFE_OPERATOR;
    }
}
