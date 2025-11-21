<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Assert\Rector\ClassMethod;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Arg;
use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Expr\FuncCall;
use Argtyper202511\PhpParser\Node\Expr\StaticCall;
use Argtyper202511\PhpParser\Node\Expr\Variable;
use Argtyper202511\PhpParser\Node\Identifier;
use Argtyper202511\PhpParser\Node\Name;
use Argtyper202511\PhpParser\Node\Name\FullyQualified;
use Argtyper202511\PhpParser\Node\Stmt\ClassMethod;
use Argtyper202511\PhpParser\Node\Stmt\Expression;
use Argtyper202511\PhpParser\PrettyPrinter\Standard;
use Argtyper202511\PHPStan\Type\ArrayType;
use Argtyper202511\PHPStan\Type\BooleanType;
use Argtyper202511\PHPStan\Type\FloatType;
use Argtyper202511\PHPStan\Type\IntegerType;
use Argtyper202511\PHPStan\Type\StringType;
use Argtyper202511\PHPStan\Type\Type;
use Argtyper202511\Rector\Assert\Enum\AssertClassName;
use Argtyper202511\Rector\Assert\NodeAnalyzer\ExistingAssertStaticCallResolver;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Argtyper202511\Rector\Contract\Rector\ConfigurableRectorInterface;
use Argtyper202511\Rector\PHPStan\ScopeFetcher;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Argtyper202511\RectorPrefix202511\Webmozart\Assert\Assert;
/**
 * @experimental Check generic array key/value types in runtime with assert. Generics for impatient people.
 *
 * @see \Rector\Tests\Assert\Rector\ClassMethod\AddAssertArrayFromClassMethodDocblockRector\AddAssertArrayFromClassMethodDocblockRectorTest
 */
final class AddAssertArrayFromClassMethodDocblockRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    /**
     * @readonly
     * @var \Rector\Assert\NodeAnalyzer\ExistingAssertStaticCallResolver
     */
    private $existingAssertStaticCallResolver;
    /**
     * @var string
     */
    private $assertClass = AssertClassName::WEBMOZART;
    public function __construct(PhpDocInfoFactory $phpDocInfoFactory, ExistingAssertStaticCallResolver $existingAssertStaticCallResolver)
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->existingAssertStaticCallResolver = $existingAssertStaticCallResolver;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add key and value assert based on docblock @param type declarations (pick from "webmozart" or "beberlei" asserts)', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
<?php

namespace Argtyper202511\RectorPrefix202511;

class SomeClass
{
    /**
     * @param int[] $items
     */
    public function run(array $items)
    {
    }
}
\class_alias('Argtyper202511\SomeClass', 'Argtyper202511\SomeClass', \false);

CODE_SAMPLE
, <<<'CODE_SAMPLE'
<?php

namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\RectorPrefix202511\Webmozart\Assert\Assert;
class SomeClass
{
    /**
     * @param int[] $items
     */
    public function run(array $items)
    {
        Assert::allInteger($items);
    }
}
\class_alias('Argtyper202511\SomeClass', 'Argtyper202511\SomeClass', \false);
CODE_SAMPLE
, [AssertClassName::WEBMOZART])]);
    }
    public function getNodeTypes(): array
    {
        return [ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?ClassMethod
    {
        $scope = ScopeFetcher::fetch($node);
        if (!$scope->isInClass()) {
            return null;
        }
        if ($node->stmts === null || $node->isAbstract()) {
            return null;
        }
        $methodPhpDocInfo = $this->phpDocInfoFactory->createFromNode($node);
        if (!$methodPhpDocInfo instanceof PhpDocInfo) {
            return null;
        }
        $paramTagValueNodes = $methodPhpDocInfo->getParamTagValueNodes();
        if ($paramTagValueNodes === []) {
            return null;
        }
        $assertStaticCallStmts = [];
        foreach ($node->getParams() as $param) {
            if (!$param->type instanceof Identifier) {
                continue;
            }
            // handle arrays only
            if (!$this->isName($param->type, 'array')) {
                continue;
            }
            if (!$param->var instanceof Variable) {
                continue;
            }
            $paramName = $param->var->name;
            if (!is_string($paramName)) {
                continue;
            }
            $paramDocType = $methodPhpDocInfo->getParamType($paramName);
            if (!$paramDocType instanceof ArrayType) {
                continue;
            }
            $valueAssertMethod = $this->matchTypeToAssertMethod($paramDocType->getItemType());
            if (is_string($valueAssertMethod)) {
                $assertStaticCallStmts[] = $this->createAssertExpression($param->var, $valueAssertMethod);
            }
            $keyAssertMethod = $this->matchTypeToAssertMethod($paramDocType->getKeyType());
            if (is_string($keyAssertMethod)) {
                $arrayKeys = new FuncCall(new Name('array_keys'), [new Arg($param->var)]);
                $assertStaticCallStmts[] = $this->createAssertExpression($arrayKeys, $keyAssertMethod);
            }
        }
        // filter existing assert to avoid duplication
        if ($assertStaticCallStmts === []) {
            return null;
        }
        $existingAssertCallHashes = $this->existingAssertStaticCallResolver->resolve($node);
        $assertStaticCallStmts = $this->filterOutExistingStaticCall($assertStaticCallStmts, $existingAssertCallHashes);
        if ($assertStaticCallStmts === []) {
            return null;
        }
        $node->stmts = array_merge($assertStaticCallStmts, $node->stmts);
        return $node;
    }
    /**
     * @param array<string> $configuration
     */
    public function configure(array $configuration): void
    {
        if ($configuration === []) {
            // default
            return;
        }
        Assert::count($configuration, 1);
        Assert::inArray($configuration[0], [AssertClassName::BEBERLEI, AssertClassName::WEBMOZART]);
        $this->assertClass = $configuration[0];
    }
    private function createAssertExpression(Expr $expr, string $methodName): Expression
    {
        $assertFullyQualified = new FullyQualified($this->assertClass);
        $staticCall = new StaticCall($assertFullyQualified, $methodName, [new Arg($expr)]);
        return new Expression($staticCall);
    }
    /**
     * @param Expression[] $assertStaticCallStmts
     * @param string[] $existingAssertCallHashes
     * @return Expression[]
     */
    private function filterOutExistingStaticCall(array $assertStaticCallStmts, array $existingAssertCallHashes): array
    {
        $standard = new Standard();
        return array_filter($assertStaticCallStmts, function (Expression $assertStaticCallExpression) use ($standard, $existingAssertCallHashes): bool {
            $currentStaticCallHash = $standard->prettyPrintExpr($assertStaticCallExpression->expr);
            return !in_array($currentStaticCallHash, $existingAssertCallHashes, \true);
        });
    }
    private function matchTypeToAssertMethod(Type $type): ?string
    {
        if ($type instanceof IntegerType) {
            return 'allInteger';
        }
        if ($type instanceof StringType) {
            return 'allString';
        }
        if ($type instanceof FloatType) {
            return 'allFloat';
        }
        if ($type instanceof BooleanType) {
            return 'allBoolean';
        }
        return null;
    }
}
