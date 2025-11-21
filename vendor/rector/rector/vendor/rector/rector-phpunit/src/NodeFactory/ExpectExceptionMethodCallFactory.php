<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\PHPUnit\NodeFactory;

use Argtyper202511\PhpParser\Node\Arg;
use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Expr\MethodCall;
use Argtyper202511\PhpParser\Node\Scalar\String_;
use Argtyper202511\PhpParser\Node\Stmt\Expression;
use Argtyper202511\PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode;
use Argtyper202511\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use Argtyper202511\Rector\Exception\ShouldNotHappenException;
use Argtyper202511\Rector\PhpParser\Node\NodeFactory;
use Argtyper202511\Rector\PHPUnit\PhpDoc\PhpDocValueToNodeMapper;
final class ExpectExceptionMethodCallFactory
{
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\NodeFactory
     */
    private $nodeFactory;
    /**
     * @readonly
     * @var \Rector\PHPUnit\PhpDoc\PhpDocValueToNodeMapper
     */
    private $phpDocValueToNodeMapper;
    public function __construct(NodeFactory $nodeFactory, PhpDocValueToNodeMapper $phpDocValueToNodeMapper)
    {
        $this->nodeFactory = $nodeFactory;
        $this->phpDocValueToNodeMapper = $phpDocValueToNodeMapper;
    }
    /**
     * @param PhpDocTagNode[] $phpDocTagNodes
     * @return Expression[]
     */
    public function createFromTagValueNodes(array $phpDocTagNodes, string $methodName) : array
    {
        $methodCallExpressions = [];
        foreach ($phpDocTagNodes as $phpDocTagNode) {
            $methodCall = $this->createMethodCall($phpDocTagNode, $methodName);
            $methodCallExpressions[] = new Expression($methodCall);
        }
        return $methodCallExpressions;
    }
    private function createMethodCall(PhpDocTagNode $phpDocTagNode, string $methodName) : MethodCall
    {
        if (!$phpDocTagNode->value instanceof GenericTagValueNode) {
            throw new ShouldNotHappenException();
        }
        $expr = $this->createExpectedExpr($phpDocTagNode, $phpDocTagNode->value);
        return $this->nodeFactory->createMethodCall('this', $methodName, [new Arg($expr)]);
    }
    private function createExpectedExpr(PhpDocTagNode $phpDocTagNode, GenericTagValueNode $genericTagValueNode) : Expr
    {
        if ($phpDocTagNode->name === '@expectedExceptionMessage') {
            return new String_($genericTagValueNode->value);
        }
        return $this->phpDocValueToNodeMapper->mapGenericTagValueNode($genericTagValueNode);
    }
}
