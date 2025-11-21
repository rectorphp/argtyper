<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Argtyper202511\Rector\NodeTypeResolver\PhpDocNodeVisitor\ClassRenamePhpDocNodeVisitor;
use Argtyper202511\Rector\NodeTypeResolver\ValueObject\OldToNewType;
use Argtyper202511\Rector\PhpDocParser\PhpDocParser\PhpDocNodeTraverser;
final class DocBlockClassRenamer
{
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\PhpDocNodeVisitor\ClassRenamePhpDocNodeVisitor
     */
    private $classRenamePhpDocNodeVisitor;
    public function __construct(ClassRenamePhpDocNodeVisitor $classRenamePhpDocNodeVisitor)
    {
        $this->classRenamePhpDocNodeVisitor = $classRenamePhpDocNodeVisitor;
    }
    /**
     * @param OldToNewType[] $oldToNewTypes
     */
    public function renamePhpDocType(PhpDocInfo $phpDocInfo, array $oldToNewTypes, Node $currentPhpNode) : bool
    {
        if ($oldToNewTypes === []) {
            return \false;
        }
        $phpDocNodeTraverser = new PhpDocNodeTraverser();
        $phpDocNodeTraverser->addPhpDocNodeVisitor($this->classRenamePhpDocNodeVisitor);
        $this->classRenamePhpDocNodeVisitor->setCurrentPhpNode($currentPhpNode);
        $this->classRenamePhpDocNodeVisitor->setOldToNewTypes($oldToNewTypes);
        $phpDocNodeTraverser->traverse($phpDocInfo->getPhpDocNode());
        return $this->classRenamePhpDocNodeVisitor->hasChanged();
    }
}
