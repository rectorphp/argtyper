<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\TypeDeclaration\Rector\ClassMethod;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr\ClassConstFetch;
use Argtyper202511\PhpParser\Node\Expr\ConstFetch;
use Argtyper202511\PhpParser\Node\Stmt\ClassMethod;
use Argtyper202511\PhpParser\Node\Stmt\Return_;
use Argtyper202511\PHPStan\Type\Type;
use Argtyper202511\Rector\NodeTypeResolver\PHPStan\Type\TypeFactory;
use Argtyper202511\Rector\PhpParser\Node\BetterNodeFinder;
use Argtyper202511\Rector\PHPStan\ScopeFetcher;
use Argtyper202511\Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Rector\StaticTypeMapper\StaticTypeMapper;
use Argtyper202511\Rector\TypeDeclaration\NodeAnalyzer\ReturnAnalyzer;
use Argtyper202511\Rector\ValueObject\PhpVersion;
use Argtyper202511\Rector\VendorLocker\NodeVendorLocker\ClassMethodReturnTypeOverrideGuard;
use Argtyper202511\Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictConstantReturnRector\ReturnTypeFromStrictConstantReturnRectorTest
 */
final class ReturnTypeFromStrictConstantReturnRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \Rector\VendorLocker\NodeVendorLocker\ClassMethodReturnTypeOverrideGuard
     */
    private $classMethodReturnTypeOverrideGuard;
    /**
     * @readonly
     * @var \Rector\StaticTypeMapper\StaticTypeMapper
     */
    private $staticTypeMapper;
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\PHPStan\Type\TypeFactory
     */
    private $typeFactory;
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\NodeAnalyzer\ReturnAnalyzer
     */
    private $returnAnalyzer;
    public function __construct(ClassMethodReturnTypeOverrideGuard $classMethodReturnTypeOverrideGuard, StaticTypeMapper $staticTypeMapper, BetterNodeFinder $betterNodeFinder, TypeFactory $typeFactory, ReturnAnalyzer $returnAnalyzer)
    {
        $this->classMethodReturnTypeOverrideGuard = $classMethodReturnTypeOverrideGuard;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->typeFactory = $typeFactory;
        $this->returnAnalyzer = $returnAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Add strict type declaration based on returned constants', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public const NAME = 'name';

    public function run()
    {
        return self::NAME;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public const NAME = 'name';

    public function run(): string
    {
        return self::NAME;
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
        return [ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node) : ?Node
    {
        $scope = ScopeFetcher::fetch($node);
        if ($node->returnType instanceof Node) {
            return null;
        }
        $returns = $this->betterNodeFinder->findReturnsScoped($node);
        if (!$this->returnAnalyzer->hasOnlyReturnWithExpr($node, $returns)) {
            return null;
        }
        if ($this->classMethodReturnTypeOverrideGuard->shouldSkipClassMethod($node, $scope)) {
            return null;
        }
        $matchedType = $this->matchAlwaysReturnConstFetch($returns);
        if (!$matchedType instanceof Type) {
            return null;
        }
        $returnTypeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($matchedType, TypeKind::RETURN);
        if (!$returnTypeNode instanceof Node) {
            return null;
        }
        $node->returnType = $returnTypeNode;
        return $node;
    }
    /**
     * @return PhpVersion::*
     */
    public function provideMinPhpVersion() : int
    {
        return PhpVersion::PHP_70;
    }
    /**
     * @param Return_[] $returns
     */
    private function matchAlwaysReturnConstFetch(array $returns) : ?Type
    {
        $classConstFetchTypes = [];
        foreach ($returns as $return) {
            if (!$return->expr instanceof ClassConstFetch && !$return->expr instanceof ConstFetch) {
                return null;
            }
            $classConstFetchTypes[] = $this->nodeTypeResolver->getType($return->expr);
        }
        return $this->typeFactory->createMixedPassedOrUnionType($classConstFetchTypes);
    }
}
