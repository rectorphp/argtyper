<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Symfony\NodeAnalyzer\Annotations;

use Argtyper202511\PhpParser\Node\Expr\MethodCall;
use Argtyper202511\PhpParser\Node\Expr\New_;
use Argtyper202511\PhpParser\Node\Stmt;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Argtyper202511\Rector\Exception\NotImplementedYetException;
use Argtyper202511\Rector\Symfony\NodeFactory\Annotations\DoctrineAnnotationFromNewFactory;
final class ClassAnnotationAssertResolver
{
    /**
     * @readonly
     * @var \Rector\Symfony\NodeAnalyzer\Annotations\StmtMethodCallMatcher
     */
    private $stmtMethodCallMatcher;
    /**
     * @readonly
     * @var \Rector\Symfony\NodeFactory\Annotations\DoctrineAnnotationFromNewFactory
     */
    private $doctrineAnnotationFromNewFactory;
    public function __construct(\Argtyper202511\Rector\Symfony\NodeAnalyzer\Annotations\StmtMethodCallMatcher $stmtMethodCallMatcher, DoctrineAnnotationFromNewFactory $doctrineAnnotationFromNewFactory)
    {
        $this->stmtMethodCallMatcher = $stmtMethodCallMatcher;
        $this->doctrineAnnotationFromNewFactory = $doctrineAnnotationFromNewFactory;
    }
    public function resolve(Stmt $stmt) : ?DoctrineAnnotationTagValueNode
    {
        $methodCall = $this->stmtMethodCallMatcher->match($stmt, 'addConstraint');
        if (!$methodCall instanceof MethodCall) {
            return null;
        }
        $args = $methodCall->getArgs();
        $firstArgValue = $args[0]->value;
        if (!$firstArgValue instanceof New_) {
            throw new NotImplementedYetException();
        }
        return $this->doctrineAnnotationFromNewFactory->create($firstArgValue);
    }
}
