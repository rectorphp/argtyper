<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Naming\ParamRenamer;

use Argtyper202511\PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Argtyper202511\Rector\BetterPhpDocParser\ValueObject\PhpDocAttributeKey;
use Argtyper202511\Rector\Comments\NodeDocBlock\DocBlockUpdater;
use Argtyper202511\Rector\Naming\ValueObject\ParamRename;
use Argtyper202511\Rector\Naming\VariableRenamer;
final class ParamRenamer
{
    /**
     * @readonly
     * @var \Rector\Naming\VariableRenamer
     */
    private $variableRenamer;
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
    public function __construct(VariableRenamer $variableRenamer, DocBlockUpdater $docBlockUpdater, PhpDocInfoFactory $phpDocInfoFactory)
    {
        $this->variableRenamer = $variableRenamer;
        $this->docBlockUpdater = $docBlockUpdater;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
    }
    public function rename(ParamRename $paramRename) : void
    {
        // 1. rename param
        $paramRename->getVariable()->name = $paramRename->getExpectedName();
        // 2. rename param in the rest of the method
        $this->variableRenamer->renameVariableInFunctionLike($paramRename->getFunctionLike(), $paramRename->getCurrentName(), $paramRename->getExpectedName());
        // 3. rename @param variable in docblock too
        $this->renameParameterNameInDocBlock($paramRename);
    }
    private function renameParameterNameInDocBlock(ParamRename $paramRename) : void
    {
        $functionLike = $paramRename->getFunctionLike();
        $phpDocInfo = $this->phpDocInfoFactory->createFromNode($functionLike);
        if (!$phpDocInfo instanceof PhpDocInfo) {
            return;
        }
        $paramTagValueNode = $phpDocInfo->getParamTagValueByName($paramRename->getCurrentName());
        if (!$paramTagValueNode instanceof ParamTagValueNode) {
            return;
        }
        $paramTagValueNode->parameterName = '$' . $paramRename->getExpectedName();
        $paramTagValueNode->setAttribute(PhpDocAttributeKey::ORIG_NODE, null);
        $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($functionLike);
    }
}
