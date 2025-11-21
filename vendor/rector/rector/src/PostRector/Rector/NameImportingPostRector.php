<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\PostRector\Rector;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Name;
use Argtyper202511\PhpParser\Node\Name\FullyQualified;
use Argtyper202511\PhpParser\Node\Stmt;
use Argtyper202511\PhpParser\Node\Stmt\GroupUse;
use Argtyper202511\PhpParser\Node\Stmt\Use_;
use Argtyper202511\Rector\CodingStyle\Node\NameImporter;
use Argtyper202511\Rector\Naming\Naming\UseImportsResolver;
use Argtyper202511\Rector\PostRector\Guard\AddUseStatementGuard;
final class NameImportingPostRector extends \Argtyper202511\Rector\PostRector\Rector\AbstractPostRector
{
    /**
     * @readonly
     * @var \Rector\CodingStyle\Node\NameImporter
     */
    private $nameImporter;
    /**
     * @readonly
     * @var \Rector\Naming\Naming\UseImportsResolver
     */
    private $useImportsResolver;
    /**
     * @readonly
     * @var \Rector\PostRector\Guard\AddUseStatementGuard
     */
    private $addUseStatementGuard;
    /**
     * @var array<Use_|GroupUse>
     */
    private $currentUses = [];
    public function __construct(NameImporter $nameImporter, UseImportsResolver $useImportsResolver, AddUseStatementGuard $addUseStatementGuard)
    {
        $this->nameImporter = $nameImporter;
        $this->useImportsResolver = $useImportsResolver;
        $this->addUseStatementGuard = $addUseStatementGuard;
    }
    /**
     * @return Stmt[]
     */
    public function beforeTraverse(array $nodes): array
    {
        $this->currentUses = $this->useImportsResolver->resolve();
        return $nodes;
    }
    public function enterNode(Node $node): ?\Argtyper202511\PhpParser\Node\Name
    {
        if (!$node instanceof FullyQualified) {
            return null;
        }
        $name = $this->nameImporter->importName($node, $this->getFile(), $this->currentUses);
        if (!$name instanceof Name) {
            return null;
        }
        $this->addRectorClassWithLine($node);
        return $name;
    }
    /**
     * @param Stmt[] $stmts
     */
    public function shouldTraverse(array $stmts): bool
    {
        return $this->addUseStatementGuard->shouldTraverse($stmts, $this->getFile()->getFilePath());
    }
}
