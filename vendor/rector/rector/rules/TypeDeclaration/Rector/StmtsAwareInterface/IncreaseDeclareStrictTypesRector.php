<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\TypeDeclaration\Rector\StmtsAwareInterface;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\DeclareItem;
use Argtyper202511\PhpParser\Node\Identifier;
use Argtyper202511\PhpParser\Node\Scalar\Int_;
use Argtyper202511\PhpParser\Node\Stmt;
use Argtyper202511\PhpParser\Node\Stmt\Declare_;
use Argtyper202511\PhpParser\Node\Stmt\Nop;
use Argtyper202511\Rector\ChangesReporting\ValueObject\RectorWithLineChange;
use Argtyper202511\Rector\Contract\PhpParser\Node\StmtsAwareInterface;
use Argtyper202511\Rector\Contract\Rector\ConfigurableRectorInterface;
use Argtyper202511\Rector\PhpParser\Node\CustomNode\FileWithoutNamespace;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Rector\TypeDeclaration\NodeAnalyzer\DeclareStrictTypeFinder;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Argtyper202511\RectorPrefix202511\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\StmtsAwareInterface\IncreaseDeclareStrictTypesRector\IncreaseDeclareStrictTypesRectorTest
 */
final class IncreaseDeclareStrictTypesRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\NodeAnalyzer\DeclareStrictTypeFinder
     */
    private $declareStrictTypeFinder;
    public const LIMIT = 'limit';
    /**
     * @var int
     */
    private $limit = 10;
    /**
     * @var int
     */
    private $changedItemCount = 0;
    public function __construct(DeclareStrictTypeFinder $declareStrictTypeFinder)
    {
        $this->declareStrictTypeFinder = $declareStrictTypeFinder;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Add declare strict types to a limited amount of classes at a time, to try out in the wild and increase level gradually', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
function someFunction()
{
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
declare(strict_types=1);

function someFunction()
{
}
CODE_SAMPLE
, [self::LIMIT => 10])]);
    }
    /**
     * @param Stmt[] $nodes
     * @return Stmt[]|null
     */
    public function beforeTraverse(array $nodes) : ?array
    {
        parent::beforeTraverse($nodes);
        if ($nodes === []) {
            return null;
        }
        $rootStmt = \current($nodes);
        $stmt = $rootStmt;
        // skip classes without namespace for safety reasons
        if ($rootStmt instanceof FileWithoutNamespace) {
            return null;
        }
        if ($this->declareStrictTypeFinder->hasDeclareStrictTypes($stmt)) {
            return null;
        }
        // keep change within a limit
        if ($this->changedItemCount >= $this->limit) {
            return null;
        }
        ++$this->changedItemCount;
        $strictTypesDeclare = $this->creteStrictTypesDeclare();
        $rectorWithLineChange = new RectorWithLineChange(self::class, $stmt->getStartLine());
        $this->file->addRectorClassWithLine($rectorWithLineChange);
        return \array_merge([$strictTypesDeclare, new Nop()], $nodes);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [StmtsAwareInterface::class];
    }
    /**
     * @param StmtsAwareInterface $node
     */
    public function refactor(Node $node) : ?Node
    {
        // workaround, as Rector now only hooks to specific nodes, not arrays
        return null;
    }
    /**
     * @param array<string, mixed> $configuration
     */
    public function configure(array $configuration) : void
    {
        Assert::keyExists($configuration, self::LIMIT);
        $this->limit = (int) $configuration[self::LIMIT];
    }
    private function creteStrictTypesDeclare() : Declare_
    {
        $declareItem = new DeclareItem(new Identifier('strict_types'), new Int_(1));
        return new Declare_([$declareItem]);
    }
}
