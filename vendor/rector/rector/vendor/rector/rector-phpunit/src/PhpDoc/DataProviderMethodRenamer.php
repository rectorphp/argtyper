<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\PHPUnit\PhpDoc;

use Argtyper202511\RectorPrefix202511\Nette\Utils\Strings;
use Argtyper202511\PhpParser\Node\Stmt\Class_;
use Argtyper202511\PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Argtyper202511\Rector\BetterPhpDocParser\ValueObject\PhpDocAttributeKey;
use Argtyper202511\Rector\Comments\NodeDocBlock\DocBlockUpdater;
final class DataProviderMethodRenamer
{
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    /**
     * @readonly
     * @var \Rector\Comments\NodeDocBlock\DocBlockUpdater
     */
    private $docBlockUpdater;
    public function __construct(PhpDocInfoFactory $phpDocInfoFactory, DocBlockUpdater $docBlockUpdater)
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->docBlockUpdater = $docBlockUpdater;
    }
    public function removeTestPrefix(Class_ $class): void
    {
        foreach ($class->getMethods() as $classMethod) {
            $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($classMethod);
            $hasClassMethodChanged = \false;
            foreach ($phpDocInfo->getTagsByName('dataProvider') as $phpDocTagNode) {
                if (!$phpDocTagNode->value instanceof GenericTagValueNode) {
                    continue;
                }
                $oldMethodName = $phpDocTagNode->value->value;
                if (strncmp($oldMethodName, 'test', strlen('test')) !== 0) {
                    continue;
                }
                $newMethodName = $this->createMethodNameWithoutPrefix($oldMethodName, 'test');
                $phpDocTagNode->value->value = Strings::replace($oldMethodName, '#' . preg_quote($oldMethodName, '#') . '#', $newMethodName);
                // invoke reprint
                $phpDocTagNode->setAttribute(PhpDocAttributeKey::START_AND_END, null);
                $hasClassMethodChanged = \true;
            }
            if ($hasClassMethodChanged) {
                $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($classMethod);
            }
        }
    }
    private function createMethodNameWithoutPrefix(string $methodName, string $prefix): string
    {
        $newMethodName = Strings::substring($methodName, strlen($prefix));
        return lcfirst($newMethodName);
    }
}
