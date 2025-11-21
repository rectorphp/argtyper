<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Php80\Rector\Class_;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Expr\Cast\String_ as CastString_;
use Argtyper202511\PhpParser\Node\Expr\Closure;
use Argtyper202511\PhpParser\Node\Identifier;
use Argtyper202511\PhpParser\Node\Name\FullyQualified;
use Argtyper202511\PhpParser\Node\Scalar\String_;
use Argtyper202511\PhpParser\Node\Stmt\Class_;
use Argtyper202511\PhpParser\Node\Stmt\ClassMethod;
use Argtyper202511\PhpParser\Node\Stmt\Function_;
use Argtyper202511\PhpParser\Node\Stmt\Return_;
use Argtyper202511\PhpParser\NodeVisitor;
use Argtyper202511\Rector\FamilyTree\Reflection\FamilyRelationsAnalyzer;
use Argtyper202511\Rector\NodeAnalyzer\ClassAnalyzer;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer;
use Argtyper202511\Rector\TypeDeclaration\TypeInferer\SilentVoidResolver;
use Argtyper202511\Rector\ValueObject\MethodName;
use Argtyper202511\Rector\ValueObject\PhpVersionFeature;
use Argtyper202511\Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Php80\Rector\Class_\StringableForToStringRector\StringableForToStringRectorTest
 */
final class StringableForToStringRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \Rector\FamilyTree\Reflection\FamilyRelationsAnalyzer
     */
    private $familyRelationsAnalyzer;
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer
     */
    private $returnTypeInferer;
    /**
     * @readonly
     * @var \Rector\NodeAnalyzer\ClassAnalyzer
     */
    private $classAnalyzer;
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\TypeInferer\SilentVoidResolver
     */
    private $silentVoidResolver;
    /**
     * @var string
     */
    private const STRINGABLE = 'Stringable';
    /**
     * @var bool
     */
    private $hasChanged = \false;
    public function __construct(FamilyRelationsAnalyzer $familyRelationsAnalyzer, ReturnTypeInferer $returnTypeInferer, ClassAnalyzer $classAnalyzer, SilentVoidResolver $silentVoidResolver)
    {
        $this->familyRelationsAnalyzer = $familyRelationsAnalyzer;
        $this->returnTypeInferer = $returnTypeInferer;
        $this->classAnalyzer = $classAnalyzer;
        $this->silentVoidResolver = $silentVoidResolver;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::STRINGABLE;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Add `Stringable` interface to classes with `__toString()` method', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function __toString()
    {
        return 'I can stringz';
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass implements Stringable
{
    public function __toString(): string
    {
        return 'I can stringz';
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
        if ($this->classAnalyzer->isAnonymousClass($node)) {
            return null;
        }
        $toStringClassMethod = $node->getMethod(MethodName::TO_STRING);
        if (!$toStringClassMethod instanceof ClassMethod) {
            return null;
        }
        $this->hasChanged = \false;
        // warning, classes that implements __toString() will return Stringable interface even if they don't implement it
        // reflection cannot be used for real detection
        $classLikeAncestorNames = $this->familyRelationsAnalyzer->getClassLikeAncestorNames($node);
        $isAncestorHasStringable = \in_array(self::STRINGABLE, $classLikeAncestorNames, \true);
        $returnType = $this->returnTypeInferer->inferFunctionLike($toStringClassMethod);
        if (!$returnType->isString()->yes()) {
            $this->processNotStringType($toStringClassMethod);
        }
        if (!$isAncestorHasStringable) {
            // add interface
            $node->implements[] = new FullyQualified(self::STRINGABLE);
            $this->hasChanged = \true;
        }
        // add return type
        if (!$toStringClassMethod->returnType instanceof Node) {
            $toStringClassMethod->returnType = new Identifier('string');
            $this->hasChanged = \true;
        }
        if (!$this->hasChanged) {
            return null;
        }
        return $node;
    }
    private function processNotStringType(ClassMethod $toStringClassMethod) : void
    {
        if ($toStringClassMethod->isAbstract()) {
            return;
        }
        if ($this->silentVoidResolver->hasSilentVoid($toStringClassMethod)) {
            $emptyStringReturn = new Return_(new String_(''));
            $toStringClassMethod->stmts[] = $emptyStringReturn;
            $this->hasChanged = \true;
            return;
        }
        $this->traverseNodesWithCallable((array) $toStringClassMethod->stmts, function (Node $subNode) : ?int {
            if ($subNode instanceof Class_ || $subNode instanceof Function_ || $subNode instanceof Closure) {
                return NodeVisitor::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
            }
            if (!$subNode instanceof Return_) {
                return null;
            }
            if (!$subNode->expr instanceof Expr) {
                $subNode->expr = new String_('');
                return null;
            }
            $type = $this->nodeTypeResolver->getType($subNode->expr);
            if ($type->isString()->yes()) {
                return null;
            }
            $subNode->expr = new CastString_($subNode->expr);
            $this->hasChanged = \true;
            return null;
        });
    }
}
