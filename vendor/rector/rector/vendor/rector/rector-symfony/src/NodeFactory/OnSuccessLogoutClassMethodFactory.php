<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Symfony\NodeFactory;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp\NotIdentical;
use Argtyper202511\PhpParser\Node\Expr\MethodCall;
use Argtyper202511\PhpParser\Node\Expr\Variable;
use Argtyper202511\PhpParser\Node\Param;
use Argtyper202511\PhpParser\Node\Stmt\ClassMethod;
use Argtyper202511\PhpParser\Node\Stmt\Expression;
use Argtyper202511\PhpParser\Node\Stmt\If_;
use Argtyper202511\PhpParser\Node\Stmt\Return_;
use Argtyper202511\PhpParser\NodeVisitor;
use Argtyper202511\Rector\NodeNameResolver\NodeNameResolver;
use Argtyper202511\Rector\PhpDocParser\NodeTraverser\SimpleCallableNodeTraverser;
use Argtyper202511\Rector\PhpParser\Node\NodeFactory;
final class OnSuccessLogoutClassMethodFactory
{
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\NodeFactory
     */
    private $nodeFactory;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\PhpDocParser\NodeTraverser\SimpleCallableNodeTraverser
     */
    private $simpleCallableNodeTraverser;
    /**
     * @readonly
     * @var \Rector\Symfony\NodeFactory\BareLogoutClassMethodFactory
     */
    private $bareLogoutClassMethodFactory;
    /**
     * @var string
     */
    private const LOGOUT_EVENT = 'logoutEvent';
    public function __construct(NodeFactory $nodeFactory, NodeNameResolver $nodeNameResolver, SimpleCallableNodeTraverser $simpleCallableNodeTraverser, \Argtyper202511\Rector\Symfony\NodeFactory\BareLogoutClassMethodFactory $bareLogoutClassMethodFactory)
    {
        $this->nodeFactory = $nodeFactory;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
        $this->bareLogoutClassMethodFactory = $bareLogoutClassMethodFactory;
    }
    public function createFromOnLogoutSuccessClassMethod(ClassMethod $onLogoutSuccessClassMethod) : ClassMethod
    {
        $classMethod = $this->bareLogoutClassMethodFactory->create();
        $getResponseMethodCall = new MethodCall(new Variable(self::LOGOUT_EVENT), 'getResponse');
        $notIdentical = new NotIdentical($getResponseMethodCall, $this->nodeFactory->createNull());
        $if = new If_($notIdentical);
        $if->stmts[] = new Return_();
        // replace `return $response;` with `$logoutEvent->setResponse($response)`
        $this->replaceReturnResponseWithSetResponse($onLogoutSuccessClassMethod);
        $this->replaceRequestWithGetRequest($onLogoutSuccessClassMethod);
        $oldClassStmts = (array) $onLogoutSuccessClassMethod->stmts;
        $classMethod->stmts = \array_merge([$if], $oldClassStmts);
        return $classMethod;
    }
    private function replaceReturnResponseWithSetResponse(ClassMethod $classMethod) : void
    {
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($classMethod, function (Node $node) : ?Expression {
            if (!$node instanceof Return_) {
                return null;
            }
            if (!$node->expr instanceof Expr) {
                return null;
            }
            $args = $this->nodeFactory->createArgs([$node->expr]);
            $methodCall = new MethodCall(new Variable(self::LOGOUT_EVENT), 'setResponse', $args);
            return new Expression($methodCall);
        });
    }
    private function replaceRequestWithGetRequest(ClassMethod $classMethod) : void
    {
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($classMethod, function (Node $node) {
            if ($node instanceof Param) {
                return NodeVisitor::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
            }
            if (!$node instanceof Variable) {
                return null;
            }
            if (!$this->nodeNameResolver->isName($node, 'request')) {
                return null;
            }
            return new MethodCall(new Variable(self::LOGOUT_EVENT), 'getRequest');
        });
    }
}
