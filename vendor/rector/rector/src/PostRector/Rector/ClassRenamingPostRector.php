<?php

declare (strict_types=1);
namespace Rector\PostRector\Rector;

use PhpParser\Node;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\NodeVisitor;
use Rector\CodingStyle\Application\UseImportsRemover;
use Rector\Configuration\RenamedClassesDataCollector;
use Rector\PhpParser\Node\FileNode;
use Rector\PostRector\Guard\AddUseStatementGuard;
use Rector\Renaming\Collector\RenamedNameCollector;
final class ClassRenamingPostRector extends \Rector\PostRector\Rector\AbstractPostRector
{
    /**
     * @readonly
     * @var \Rector\Configuration\RenamedClassesDataCollector
     */
    private $renamedClassesDataCollector;
    /**
     * @readonly
     * @var \Rector\CodingStyle\Application\UseImportsRemover
     */
    private $useImportsRemover;
    /**
     * @readonly
     * @var \Rector\Renaming\Collector\RenamedNameCollector
     */
    private $renamedNameCollector;
    /**
     * @readonly
     * @var \Rector\PostRector\Guard\AddUseStatementGuard
     */
    private $addUseStatementGuard;
    /**
     * @var array<string, string>
     */
    private $oldToNewClasses = [];
    public function __construct(RenamedClassesDataCollector $renamedClassesDataCollector, UseImportsRemover $useImportsRemover, RenamedNameCollector $renamedNameCollector, AddUseStatementGuard $addUseStatementGuard)
    {
        $this->renamedClassesDataCollector = $renamedClassesDataCollector;
        $this->useImportsRemover = $useImportsRemover;
        $this->renamedNameCollector = $renamedNameCollector;
        $this->addUseStatementGuard = $addUseStatementGuard;
    }
    /**
     * @return \PhpParser\Node\Stmt\Namespace_|\Rector\PhpParser\Node\FileNode|int|null
     */
    public function enterNode(Node $node)
    {
        if ($node instanceof FileNode) {
            // handle in Namespace_ node
            if ($node->isNamespaced()) {
                return null;
            }
            // handle here
            $removedUses = $this->renamedClassesDataCollector->getOldClasses();
            if ($this->useImportsRemover->removeImportsFromStmts($node, $removedUses)) {
                $this->addRectorClassWithLine($node);
            }
            $this->renamedNameCollector->reset();
            return $node;
        }
        if ($node instanceof Namespace_) {
            $removedUses = $this->renamedClassesDataCollector->getOldClasses();
            if ($this->useImportsRemover->removeImportsFromStmts($node, $removedUses)) {
                $this->addRectorClassWithLine($node);
            }
            $this->renamedNameCollector->reset();
            return $node;
        }
        // nothing else to handle here, as first 2 nodes we'll hit are handled above
        return NodeVisitor::STOP_TRAVERSAL;
    }
    public function shouldTraverse(array $stmts): bool
    {
        $this->oldToNewClasses = $this->renamedClassesDataCollector->getOldToNewClasses();
        if ($this->oldToNewClasses === []) {
            return \false;
        }
        return $this->addUseStatementGuard->shouldTraverse($stmts, $this->getFile()->getFilePath());
    }
}
