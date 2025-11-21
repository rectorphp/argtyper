<?php

declare (strict_types=1);
namespace Rector\Symfony\Symfony51\Rector\ClassMethod;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr\ClassConstFetch;
use Argtyper202511\PhpParser\Node\Scalar\Int_;
use Argtyper202511\PhpParser\Node\Stmt\ClassMethod;
use Argtyper202511\PhpParser\Node\Stmt\Return_;
use Argtyper202511\PHPStan\Reflection\ClassReflection;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\Rector\AbstractRector;
use Rector\Reflection\ReflectionResolver;
use Rector\Symfony\ValueObject\ConstantMap\SymfonyCommandConstantMap;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * https://symfony.com/blog/new-in-symfony-5-1-misc-improvements-part-1#added-constants-for-command-exit-codes
 *
 * @see \Rector\Symfony\Tests\Symfony51\Rector\ClassMethod\CommandConstantReturnCodeRector\CommandConstantReturnCodeRectorTest
 */
final class CommandConstantReturnCodeRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    public function __construct(ReflectionResolver $reflectionResolver, BetterNodeFinder $betterNodeFinder)
    {
        $this->reflectionResolver = $reflectionResolver;
        $this->betterNodeFinder = $betterNodeFinder;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Changes int return from execute to use Symfony Command constants.', [new CodeSample(<<<'CODE_SAMPLE'
class SomeCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        return 0;
    }

}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        return \Symfony\Component\Console\Command\Command::SUCCESS;
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
        return [ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        $classReflection = $this->reflectionResolver->resolveClassReflection($node);
        if (!$classReflection instanceof ClassReflection) {
            return null;
        }
        if (!$classReflection->is('Argtyper202511\Symfony\Component\Console\Command\Command')) {
            return null;
        }
        if (!$this->isName($node, 'execute')) {
            return null;
        }
        $hasChanged = \false;
        /** @var Return_[] $returns */
        $returns = $this->betterNodeFinder->findInstancesOfInFunctionLikeScoped($node, [Return_::class]);
        foreach ($returns as $return) {
            if (!$return->expr instanceof Int_) {
                continue;
            }
            $classConstFetch = $this->convertNumberToConstant($return->expr);
            if (!$classConstFetch instanceof ClassConstFetch) {
                continue;
            }
            $hasChanged = \true;
            $return->expr = $classConstFetch;
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    private function convertNumberToConstant(Int_ $int): ?ClassConstFetch
    {
        if (!isset(SymfonyCommandConstantMap::RETURN_TO_CONST[$int->value])) {
            return null;
        }
        return $this->nodeFactory->createClassConstFetch('Argtyper202511\Symfony\Component\Console\Command\Command', SymfonyCommandConstantMap::RETURN_TO_CONST[$int->value]);
    }
}
