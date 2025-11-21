<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Symfony\CodeQuality\Rector\ClassMethod;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr\Variable;
use Argtyper202511\PhpParser\Node\Stmt\Class_;
use Argtyper202511\PhpParser\Node\Stmt\ClassMethod;
use Argtyper202511\PHPStan\Reflection\ClassReflection;
use Argtyper202511\PHPStan\Type\ObjectType;
use Argtyper202511\Rector\DeadCode\NodeAnalyzer\IsClassMethodUsedAnalyzer;
use Argtyper202511\Rector\FamilyTree\NodeAnalyzer\ClassChildAnalyzer;
use Argtyper202511\Rector\PhpParser\Node\BetterNodeFinder;
use Argtyper202511\Rector\PHPStan\ScopeFetcher;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Rector\Reflection\ReflectionResolver;
use Argtyper202511\Rector\Symfony\TypeAnalyzer\ControllerAnalyzer;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Symfony\Tests\CodeQuality\Rector\ClassMethod\RemoveUnusedRequestParamRector\RemoveUnusedRequestParamRectorTest
 */
final class RemoveUnusedRequestParamRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Symfony\TypeAnalyzer\ControllerAnalyzer
     */
    private $controllerAnalyzer;
    /**
     * @readonly
     * @var \Rector\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    /**
     * @readonly
     * @var \Rector\FamilyTree\NodeAnalyzer\ClassChildAnalyzer
     */
    private $classChildAnalyzer;
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\DeadCode\NodeAnalyzer\IsClassMethodUsedAnalyzer
     */
    private $isClassMethodUsedAnalyzer;
    public function __construct(ControllerAnalyzer $controllerAnalyzer, ReflectionResolver $reflectionResolver, ClassChildAnalyzer $classChildAnalyzer, BetterNodeFinder $betterNodeFinder, IsClassMethodUsedAnalyzer $isClassMethodUsedAnalyzer)
    {
        $this->controllerAnalyzer = $controllerAnalyzer;
        $this->reflectionResolver = $reflectionResolver;
        $this->classChildAnalyzer = $classChildAnalyzer;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->isClassMethodUsedAnalyzer = $isClassMethodUsedAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Remove unused $request parameter from controller action', [new CodeSample(<<<'CODE_SAMPLE'
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

final class SomeController extends Controller
{
    public function run(Request $request, int $id)
    {
        echo $id;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

final class SomeController extends Controller
{
    public function run(int $id)
    {
        echo $id;
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
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->controllerAnalyzer->isInsideController($node)) {
            return null;
        }
        $hasChanged = \false;
        foreach ($node->getMethods() as $classMethod) {
            if (!$classMethod->isPublic()) {
                continue;
            }
            if ($classMethod->isAbstract() || $this->hasAbstractParentClassMethod($classMethod)) {
                continue;
            }
            if ($classMethod->getParams() === []) {
                continue;
            }
            // skip empty method
            if ($classMethod->stmts === null) {
                continue;
            }
            foreach ($classMethod->getParams() as $paramPosition => $param) {
                if (!$param->type instanceof Node) {
                    continue;
                }
                if (!$this->isObjectType($param->type, new ObjectType('Argtyper202511\\Symfony\\Component\\HttpFoundation\\Request'))) {
                    continue;
                }
                /** @var string $requestParamName */
                $requestParamName = $this->getName($param);
                // we have request param here
                $requestVariable = $this->betterNodeFinder->findVariableOfName($classMethod->stmts, $requestParamName);
                // is variable used?
                if ($requestVariable instanceof Variable) {
                    continue 2;
                }
                $scope = ScopeFetcher::fetch($node);
                if ($this->isClassMethodUsedAnalyzer->isClassMethodUsed($node, $classMethod, $scope)) {
                    continue 2;
                }
                unset($classMethod->params[$paramPosition]);
                $hasChanged = \true;
            }
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    private function hasAbstractParentClassMethod(ClassMethod $classMethod) : bool
    {
        $classReflection = $this->reflectionResolver->resolveClassReflection($classMethod);
        if (!$classReflection instanceof ClassReflection) {
            return \false;
        }
        return $this->classChildAnalyzer->hasAbstractParentClassMethod($classReflection, $this->getName($classMethod));
    }
}
