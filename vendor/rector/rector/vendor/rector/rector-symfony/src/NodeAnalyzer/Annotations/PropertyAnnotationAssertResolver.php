<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Symfony\NodeAnalyzer\Annotations;

use Argtyper202511\PhpParser\Node\Expr\MethodCall;
use Argtyper202511\PhpParser\Node\Expr\New_;
use Argtyper202511\PhpParser\Node\Stmt;
use Argtyper202511\Rector\PhpParser\Node\Value\ValueResolver;
use Argtyper202511\Rector\Symfony\NodeFactory\Annotations\DoctrineAnnotationFromNewFactory;
use Argtyper202511\Rector\Symfony\ValueObject\ValidatorAssert\PropertyAndAnnotation;
final class PropertyAnnotationAssertResolver
{
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\Value\ValueResolver
     */
    private $valueResolver;
    /**
     * @readonly
     * @var \Rector\Symfony\NodeFactory\Annotations\DoctrineAnnotationFromNewFactory
     */
    private $doctrineAnnotationFromNewFactory;
    /**
     * @readonly
     * @var \Rector\Symfony\NodeAnalyzer\Annotations\StmtMethodCallMatcher
     */
    private $stmtMethodCallMatcher;
    public function __construct(ValueResolver $valueResolver, DoctrineAnnotationFromNewFactory $doctrineAnnotationFromNewFactory, \Argtyper202511\Rector\Symfony\NodeAnalyzer\Annotations\StmtMethodCallMatcher $stmtMethodCallMatcher)
    {
        $this->valueResolver = $valueResolver;
        $this->doctrineAnnotationFromNewFactory = $doctrineAnnotationFromNewFactory;
        $this->stmtMethodCallMatcher = $stmtMethodCallMatcher;
    }
    public function resolve(Stmt $stmt): ?PropertyAndAnnotation
    {
        $methodCall = $this->stmtMethodCallMatcher->match($stmt, 'addPropertyConstraint');
        if (!$methodCall instanceof MethodCall) {
            return null;
        }
        $args = $methodCall->getArgs();
        $constraintsExpr = $args[1]->value;
        $propertyName = $this->valueResolver->getValue($args[0]->value);
        if (!is_string($propertyName)) {
            return null;
        }
        if (!$constraintsExpr instanceof New_) {
            // nothing we can do... or can we?
            return null;
        }
        $doctrineAnnotationTagValueNode = $this->doctrineAnnotationFromNewFactory->create($constraintsExpr);
        return new PropertyAndAnnotation($propertyName, $doctrineAnnotationTagValueNode);
    }
}
