<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Renaming\Rector\ClassMethod;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Stmt\Class_;
use Argtyper202511\PhpParser\Node\Stmt\ClassMethod;
use Argtyper202511\PhpParser\Node\Stmt\Expression;
use Argtyper202511\PhpParser\Node\Stmt\Property;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Argtyper202511\Rector\Comments\NodeDocBlock\DocBlockUpdater;
use Argtyper202511\Rector\Contract\Rector\ConfigurableRectorInterface;
use Argtyper202511\Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockTagReplacer;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Rector\Renaming\Contract\RenameAnnotationInterface;
use Argtyper202511\Rector\Renaming\ValueObject\RenameAnnotationByType;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Argtyper202511\RectorPrefix202511\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\Renaming\Rector\ClassMethod\RenameAnnotationRector\RenameAnnotationRectorTest
 */
final class RenameAnnotationRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockTagReplacer
     */
    private $docBlockTagReplacer;
    /**
     * @readonly
     * @var \Rector\Comments\NodeDocBlock\DocBlockUpdater
     */
    private $docBlockUpdater;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    /**
     * @var RenameAnnotationInterface[]
     */
    private $renameAnnotations = [];
    public function __construct(DocBlockTagReplacer $docBlockTagReplacer, DocBlockUpdater $docBlockUpdater, PhpDocInfoFactory $phpDocInfoFactory)
    {
        $this->docBlockTagReplacer = $docBlockTagReplacer;
        $this->docBlockUpdater = $docBlockUpdater;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Turn defined annotations above properties and methods to their new values', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    /**
     * @test
     */
    public function someMethod()
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    /**
     * @scenario
     */
    public function someMethod()
    {
    }
}
CODE_SAMPLE
, [new RenameAnnotationByType('Argtyper202511\PHPUnit\Framework\TestCase', 'test', 'scenario')])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Class_::class, Expression::class];
    }
    /**
     * @param Class_|Expression $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node instanceof Expression) {
            return $this->refactorExpression($node);
        }
        $hasChanged = \false;
        foreach ($node->stmts as $stmt) {
            if (!$stmt instanceof ClassMethod && !$stmt instanceof Property) {
                continue;
            }
            $phpDocInfo = $this->phpDocInfoFactory->createFromNode($stmt);
            if (!$phpDocInfo instanceof PhpDocInfo) {
                continue;
            }
            foreach ($this->renameAnnotations as $renameAnnotation) {
                if ($renameAnnotation instanceof RenameAnnotationByType && !$this->isObjectType($node, $renameAnnotation->getObjectType())) {
                    continue;
                }
                $hasDocBlockChanged = $this->docBlockTagReplacer->replaceTagByAnother($phpDocInfo, $renameAnnotation->getOldAnnotation(), $renameAnnotation->getNewAnnotation());
                if ($hasDocBlockChanged) {
                    $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($stmt);
                    $hasChanged = \true;
                }
            }
        }
        if (!$hasChanged) {
            return null;
        }
        return $node;
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration): void
    {
        Assert::allIsAOf($configuration, RenameAnnotationInterface::class);
        $this->renameAnnotations = $configuration;
    }
    private function refactorExpression(Expression $expression): ?Expression
    {
        $hasChanged = \false;
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($expression);
        foreach ($this->renameAnnotations as $renameAnnotation) {
            $hasDocBlockChanged = $this->docBlockTagReplacer->replaceTagByAnother($phpDocInfo, $renameAnnotation->getOldAnnotation(), $renameAnnotation->getNewAnnotation());
            if ($hasDocBlockChanged) {
                $hasChanged = \true;
            }
        }
        if ($hasChanged) {
            $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($expression);
            return $expression;
        }
        return null;
    }
}
