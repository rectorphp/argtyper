<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Php74\Rector\StaticCall;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Arg;
use Argtyper202511\PhpParser\Node\Expr\Cast\String_;
use Argtyper202511\PhpParser\Node\Expr\New_;
use Argtyper202511\PhpParser\Node\Expr\StaticCall;
use Argtyper202511\PhpParser\Node\Name;
use Argtyper202511\PHPStan\Type\ObjectType;
use Argtyper202511\Rector\PhpParser\Node\Value\ValueResolver;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Rector\ValueObject\PhpVersionFeature;
use Argtyper202511\Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Php74\Rector\StaticCall\ExportToReflectionFunctionRector\ExportToReflectionFunctionRectorTest
 */
final class ExportToReflectionFunctionRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\Value\ValueResolver
     */
    private $valueResolver;
    public function __construct(ValueResolver $valueResolver)
    {
        $this->valueResolver = $valueResolver;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::EXPORT_TO_REFLECTION_FUNCTION;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change export() to ReflectionFunction alternatives', [new CodeSample(<<<'CODE_SAMPLE'
$reflectionFunction = ReflectionFunction::export('foo');
$reflectionFunctionAsString = ReflectionFunction::export('foo', true);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$reflectionFunction = new ReflectionFunction('foo');
$reflectionFunctionAsString = (string) new ReflectionFunction('foo');
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [StaticCall::class];
    }
    /**
     * @param StaticCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$node->class instanceof Name) {
            return null;
        }
        $callerType = $this->nodeTypeResolver->getType($node->class);
        if (!$callerType->isSuperTypeOf(new ObjectType('ReflectionFunction'))->yes()) {
            return null;
        }
        if (!$this->isName($node->name, 'export')) {
            return null;
        }
        if ($node->isFirstClassCallable()) {
            return null;
        }
        $firstArg = $node->getArgs()[0] ?? null;
        if (!$firstArg instanceof Arg) {
            return null;
        }
        $new = new New_($node->class, [new Arg($firstArg->value)]);
        $secondArg = $node->getArgs()[1] ?? null;
        if (!$secondArg instanceof Arg) {
            return $new;
        }
        if ($this->valueResolver->isTrue($secondArg->value)) {
            return new String_($new);
        }
        return $new;
    }
}
