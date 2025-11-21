<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Symfony\Symfony62\Rector\ClassMethod\ClassMethod;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp\Identical;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp\NotIdentical;
use Argtyper202511\PhpParser\Node\Expr\ConstFetch;
use Argtyper202511\PhpParser\Node\Name;
use Argtyper202511\PhpParser\Node\Name\FullyQualified;
use Argtyper202511\PhpParser\Node\Stmt\Class_;
use Argtyper202511\PhpParser\Node\Stmt\ClassMethod;
use Argtyper202511\PhpParser\Node\Stmt\If_;
use Argtyper202511\PhpParser\Node\Stmt\Return_;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Rector\Symfony\Enum\SymfonyClass;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Symfony\Tests\Symfony62\Rector\ClassMethod\ArgumentValueResolverToValueResolverRector\ArgumentValueResolverToValueResolverRectorTest
 */
final class ArgumentValueResolverToValueResolverRector extends AbstractRector
{
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if (!$this->shouldRefactorClass($node)) {
            return null;
        }
        foreach ($node->getMethods() as $key => $classMethod) {
            if ($this->isName($classMethod->name, 'supports')) {
                [$isIdentical, $supportFirstArg, $supportSecondArg] = $this->extractSupportsArguments($node, $key, $classMethod);
            }
            if ($this->isName($classMethod->name, 'resolve') && isset($isIdentical) && isset($supportFirstArg) && isset($supportSecondArg)) {
                $this->processResolveMethod($classMethod, $isIdentical, $supportFirstArg, $supportSecondArg);
            }
        }
        return $node;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Replaces ArgumentValueResolverInterface by ValueResolverInterface with supports logic moved to resolve() method', [new CodeSample(<<<'CODE_SAMPLE'
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;

final class EntityValueResolver implements ArgumentValueResolverInterface
{
    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
    }

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;

final class EntityValueResolver implements ValueResolverInterface
{
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
    }
}
CODE_SAMPLE
)]);
    }
    private function shouldRefactorClass(Class_ $class): bool
    {
        // Check if the class implements ArgumentValueResolverInterface
        foreach ($class->implements as $key => $interface) {
            if ($interface->toString() === SymfonyClass::ARGUMENT_RESOLVER_INTERFACE) {
                $class->implements[$key] = new FullyQualified(SymfonyClass::VALUE_RESOLVER_INTERFACE);
                return \true;
            }
        }
        // If it doesn't implement ArgumentValueResolverInterface, skip
        return \false;
    }
    /**
     * @return array{bool, Expr|null, Expr|null}
     */
    private function extractSupportsArguments(Class_ $class, int $key, ClassMethod $classMethod): array
    {
        $isIdentical = \true;
        $supportFirstArg = $supportSecondArg = null;
        if ($classMethod->getStmts() === null) {
            return [$isIdentical, $supportFirstArg, $supportSecondArg];
        }
        foreach ($classMethod->getStmts() as $stmt) {
            if (!$stmt instanceof Return_) {
                continue;
            }
            $expression = $stmt->expr;
            if (!$expression instanceof BinaryOp) {
                continue;
            }
            if ($expression instanceof NotIdentical) {
                $isIdentical = \false;
            }
            $supportFirstArg = $expression->left;
            $supportSecondArg = $expression->right;
            unset($class->stmts[$key]);
            break;
            // We only need the first matching condition
        }
        return [$isIdentical, $supportFirstArg, $supportSecondArg];
    }
    private function processResolveMethod(ClassMethod $classMethod, bool $isIdentical, Expr $supportFirstArg, Expr $supportSecondArg): void
    {
        $ifCondition = $isIdentical ? new NotIdentical($supportFirstArg, $supportSecondArg) : new Identical($supportFirstArg, $supportSecondArg);
        $classMethod->stmts = array_merge([new If_($ifCondition, ['stmts' => [new Return_(new ConstFetch(new Name('[]')))]])], (array) $classMethod->stmts);
    }
}
