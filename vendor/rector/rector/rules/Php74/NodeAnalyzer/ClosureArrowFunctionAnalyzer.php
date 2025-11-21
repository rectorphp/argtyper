<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Php74\NodeAnalyzer;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\ClosureUse;
use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Expr\Closure;
use Argtyper202511\PhpParser\Node\Expr\FuncCall;
use Argtyper202511\PhpParser\Node\Expr\Variable;
use Argtyper202511\PhpParser\Node\Stmt\Return_;
use Argtyper202511\PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use Argtyper202511\PHPStan\Type\MixedType;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Argtyper202511\Rector\NodeAnalyzer\CompactFuncCallAnalyzer;
use Argtyper202511\Rector\NodeTypeResolver\NodeTypeResolver;
use Argtyper202511\Rector\PhpParser\Comparing\NodeComparator;
use Argtyper202511\Rector\PhpParser\Node\BetterNodeFinder;
use Argtyper202511\Rector\Util\ArrayChecker;
final class ClosureArrowFunctionAnalyzer
{
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\PhpParser\Comparing\NodeComparator
     */
    private $nodeComparator;
    /**
     * @readonly
     * @var \Rector\Util\ArrayChecker
     */
    private $arrayChecker;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    /**
     * @readonly
     * @var \Rector\NodeAnalyzer\CompactFuncCallAnalyzer
     */
    private $compactFuncCallAnalyzer;
    public function __construct(BetterNodeFinder $betterNodeFinder, NodeComparator $nodeComparator, ArrayChecker $arrayChecker, PhpDocInfoFactory $phpDocInfoFactory, NodeTypeResolver $nodeTypeResolver, CompactFuncCallAnalyzer $compactFuncCallAnalyzer)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeComparator = $nodeComparator;
        $this->arrayChecker = $arrayChecker;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->compactFuncCallAnalyzer = $compactFuncCallAnalyzer;
    }
    public function matchArrowFunctionExpr(Closure $closure) : ?Expr
    {
        if (\count($closure->stmts) !== 1) {
            return null;
        }
        $onlyStmt = $closure->stmts[0];
        if (!$onlyStmt instanceof Return_) {
            return null;
        }
        /** @var Return_ $return */
        $return = $onlyStmt;
        if (!$return->expr instanceof Expr) {
            return null;
        }
        if ($this->shouldSkipForUsedReferencedValue($closure)) {
            return null;
        }
        if ($this->shouldSkipForUseVariableUsedByCompact($closure)) {
            return null;
        }
        if ($this->shouldSkipMoreSpecificTypeWithVarDoc($return, $return->expr)) {
            return null;
        }
        return $return->expr;
    }
    private function shouldSkipForUseVariableUsedByCompact(Closure $closure) : bool
    {
        $variables = \array_map(function (ClosureUse $use) : Variable {
            return $use->var;
        }, $closure->uses);
        if ($variables === []) {
            return \false;
        }
        return (bool) $this->betterNodeFinder->findFirstInFunctionLikeScoped($closure, function (Node $node) use($variables) : bool {
            if (!$node instanceof FuncCall) {
                return \false;
            }
            foreach ($variables as $variable) {
                if ($this->compactFuncCallAnalyzer->isInCompact($node, $variable)) {
                    return \true;
                }
            }
            return \false;
        });
    }
    /**
     * Ensure @var doc usage with more specific type on purpose to be skipped
     */
    private function shouldSkipMoreSpecificTypeWithVarDoc(Return_ $return, Expr $expr) : bool
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNode($return);
        if (!$phpDocInfo instanceof PhpDocInfo) {
            return \false;
        }
        $varTagValueNode = $phpDocInfo->getVarTagValueNode();
        if (!$varTagValueNode instanceof VarTagValueNode) {
            return \false;
        }
        $varType = $phpDocInfo->getVarType();
        if ($varType instanceof MixedType) {
            return \false;
        }
        $variableName = \ltrim($varTagValueNode->variableName, '$');
        $variable = $this->betterNodeFinder->findFirst($expr, static function (Node $node) use($variableName) : bool {
            return $node instanceof Variable && $node->name === $variableName;
        });
        if (!$variable instanceof Variable) {
            return \false;
        }
        $nativeVariableType = $this->nodeTypeResolver->getNativeType($variable);
        // not equal with native type means more specific type
        return !$nativeVariableType->equals($varType);
    }
    private function shouldSkipForUsedReferencedValue(Closure $closure) : bool
    {
        $referencedValues = $this->resolveReferencedUseVariablesFromClosure($closure);
        if ($referencedValues === []) {
            return \false;
        }
        $isFoundInStmt = (bool) $this->betterNodeFinder->findFirstInFunctionLikeScoped($closure, function (Node $node) use($referencedValues) : bool {
            foreach ($referencedValues as $referencedValue) {
                if ($this->nodeComparator->areNodesEqual($node, $referencedValue)) {
                    return \true;
                }
            }
            return \false;
        });
        if ($isFoundInStmt) {
            return \true;
        }
        return $this->isFoundInInnerUses($closure, $referencedValues);
    }
    /**
     * @param Variable[] $referencedValues
     */
    private function isFoundInInnerUses(Closure $node, array $referencedValues) : bool
    {
        return (bool) $this->betterNodeFinder->findFirstInFunctionLikeScoped($node, function (Node $subNode) use($referencedValues) : bool {
            if (!$subNode instanceof Closure) {
                return \false;
            }
            foreach ($referencedValues as $referencedValue) {
                $isFoundInInnerUses = $this->arrayChecker->doesExist($subNode->uses, function (ClosureUse $closureUse) use($referencedValue) : bool {
                    return $closureUse->byRef && $this->nodeComparator->areNodesEqual($closureUse->var, $referencedValue);
                });
                if ($isFoundInInnerUses) {
                    return \true;
                }
            }
            return \false;
        });
    }
    /**
     * @return Variable[]
     */
    private function resolveReferencedUseVariablesFromClosure(Closure $closure) : array
    {
        $referencedValues = [];
        /** @var ClosureUse $use */
        foreach ($closure->uses as $use) {
            if ($use->byRef) {
                $referencedValues[] = $use->var;
            }
        }
        return $referencedValues;
    }
}
