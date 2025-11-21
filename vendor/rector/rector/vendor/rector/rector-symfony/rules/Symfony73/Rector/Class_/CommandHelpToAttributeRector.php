<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Symfony\Symfony73\Rector\Class_;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Arg;
use Argtyper202511\PhpParser\Node\Attribute;
use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Expr\MethodCall;
use Argtyper202511\PhpParser\Node\Expr\Variable;
use Argtyper202511\PhpParser\Node\Identifier;
use Argtyper202511\PhpParser\Node\Scalar\String_;
use Argtyper202511\PhpParser\Node\Stmt;
use Argtyper202511\PhpParser\Node\Stmt\Class_;
use Argtyper202511\PhpParser\Node\Stmt\ClassMethod;
use Argtyper202511\PhpParser\Node\Stmt\Expression;
use Argtyper202511\PhpParser\Node\Stmt\Function_;
use Argtyper202511\PhpParser\NodeVisitor;
use Argtyper202511\PHPStan\Reflection\ReflectionProvider;
use Argtyper202511\PHPStan\Type\ObjectType;
use Argtyper202511\Rector\Doctrine\NodeAnalyzer\AttributeFinder;
use Argtyper202511\Rector\NodeTypeResolver\Node\AttributeKey;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Rector\Symfony\Enum\CommandMethodName;
use Argtyper202511\Rector\Symfony\Enum\SymfonyAttribute;
use Argtyper202511\Rector\Symfony\Enum\SymfonyClass;
use Argtyper202511\Rector\ValueObject\PhpVersionFeature;
use Argtyper202511\Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://symfony.com/doc/current/console.html#help-message
 *
 * @see \Rector\Symfony\Tests\Symfony73\Rector\Class_\CommandHelpToAttributeRector\CommandHelpToAttributeRectorTest
 */
final class CommandHelpToAttributeRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    /**
     * @readonly
     * @var \Rector\Doctrine\NodeAnalyzer\AttributeFinder
     */
    private $attributeFinder;
    public function __construct(ReflectionProvider $reflectionProvider, AttributeFinder $attributeFinder)
    {
        $this->reflectionProvider = $reflectionProvider;
        $this->attributeFinder = $attributeFinder;
    }
    public function provideMinPhpVersion(): int
    {
        return PhpVersionFeature::ATTRIBUTES;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Moves $this->setHelp() to the "help" named argument of #[AsCommand]', [new CodeSample(<<<'CODE_SAMPLE'
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;

#[AsCommand(name: 'app:some')]
final class SomeCommand extends Command
{
    protected function configure(): void
    {
        $this->setHelp('Some help text');
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;

#[AsCommand(name: 'app:some', help: <<<'TXT'
Some help text
TXT)]
final class SomeCommand extends Command
{
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node->isAbstract()) {
            return null;
        }
        if (!$this->reflectionProvider->hasClass(SymfonyAttribute::AS_COMMAND)) {
            return null;
        }
        if (!$this->isObjectType($node, new ObjectType(SymfonyClass::COMMAND))) {
            return null;
        }
        $asCommandAttribute = $this->attributeFinder->findAttributeByClass($node, SymfonyAttribute::AS_COMMAND);
        if (!$asCommandAttribute instanceof Attribute) {
            return null;
        }
        foreach ($asCommandAttribute->args as $arg) {
            if ((($nullsafeVariable1 = $arg->name) ? $nullsafeVariable1->toString() : null) === 'help') {
                return null;
            }
        }
        $configureClassMethod = $node->getMethod(CommandMethodName::CONFIGURE);
        if (!$configureClassMethod instanceof ClassMethod) {
            return null;
        }
        $helpExpr = $this->findAndRemoveSetHelpExpr($configureClassMethod);
        if (!$helpExpr instanceof String_) {
            return null;
        }
        $wrappedHelpString = new String_($helpExpr->value, [Attributekey::KIND => String_::KIND_NOWDOC, AttributeKey::DOC_LABEL => 'TXT']);
        $asCommandAttribute->args[] = new Arg($wrappedHelpString, \false, \false, [], new Identifier('help'));
        if ($configureClassMethod->stmts === []) {
            unset($configureClassMethod);
        }
        return $node;
    }
    /**
     * Returns the argument passed to setHelp() and removes the MethodCall node.
     */
    private function findAndRemoveSetHelpExpr(ClassMethod $configureClassMethod): ?String_
    {
        $helpString = null;
        $this->traverseNodesWithCallable((array) $configureClassMethod->stmts, function (Node $node) use (&$helpString) {
            if ($node instanceof Class_ || $node instanceof Function_) {
                return NodeVisitor::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
            }
            if (!$node instanceof MethodCall) {
                return null;
            }
            if (!$this->isName($node->name, 'setHelp')) {
                return null;
            }
            if ($node->isFirstClassCallable() || !isset($node->getArgs()[0])) {
                return null;
            }
            $argExpr = $node->getArgs()[0]->value;
            if ($argExpr instanceof String_) {
                $helpString = $argExpr;
            }
            $parent = $node->getAttribute('parent');
            if ($parent instanceof Expression) {
                unset($parent);
            }
            return $node->var;
        });
        foreach ((array) $configureClassMethod->stmts as $key => $stmt) {
            if ($this->isExpressionVariableThis($stmt)) {
                unset($configureClassMethod->stmts[$key]);
            }
        }
        return $helpString;
    }
    private function isExpressionVariableThis(Stmt $stmt): bool
    {
        if (!$stmt instanceof Expression) {
            return \false;
        }
        if (!$stmt->expr instanceof Variable) {
            return \false;
        }
        return $this->isName($stmt->expr, 'this');
    }
}
