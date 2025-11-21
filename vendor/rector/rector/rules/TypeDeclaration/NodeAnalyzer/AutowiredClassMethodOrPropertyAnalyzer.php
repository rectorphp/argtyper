<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\TypeDeclaration\NodeAnalyzer;

use Argtyper202511\PhpParser\Node\Param;
use Argtyper202511\PhpParser\Node\Stmt\Class_;
use Argtyper202511\PhpParser\Node\Stmt\ClassMethod;
use Argtyper202511\PhpParser\Node\Stmt\Property;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Argtyper202511\Rector\Php80\NodeAnalyzer\PhpAttributeAnalyzer;
final class AutowiredClassMethodOrPropertyAnalyzer
{
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    /**
     * @readonly
     * @var \Rector\Php80\NodeAnalyzer\PhpAttributeAnalyzer
     */
    private $phpAttributeAnalyzer;
    public function __construct(PhpDocInfoFactory $phpDocInfoFactory, PhpAttributeAnalyzer $phpAttributeAnalyzer)
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->phpAttributeAnalyzer = $phpAttributeAnalyzer;
    }
    public function matchAutowiredMethodInClass(Class_ $class): ?ClassMethod
    {
        foreach ($class->getMethods() as $classMethod) {
            if (!$classMethod->isPublic()) {
                continue;
            }
            if ($classMethod->isMagic()) {
                continue;
            }
            if (!$this->detect($classMethod)) {
                continue;
            }
            return $classMethod;
        }
        return null;
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Param|\PhpParser\Node\Stmt\Property $node
     */
    public function detect($node): bool
    {
        $nodePhpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        if ($nodePhpDocInfo->hasByNames(['required', 'inject'])) {
            return \true;
        }
        return $this->phpAttributeAnalyzer->hasPhpAttributes($node, ['Argtyper202511\Symfony\Contracts\Service\Attribute\Required', 'Argtyper202511\Nette\DI\Attributes\Inject']);
    }
}
