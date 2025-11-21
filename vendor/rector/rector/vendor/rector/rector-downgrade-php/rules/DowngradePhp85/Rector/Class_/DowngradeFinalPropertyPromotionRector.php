<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\DowngradePhp85\Rector\Class_;

use Argtyper202511\PhpParser\Builder\Property;
use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Param;
use Argtyper202511\PhpParser\Node\Stmt\ClassMethod;
use Argtyper202511\PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode;
use Argtyper202511\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Argtyper202511\Rector\Comments\NodeDocBlock\DocBlockUpdater;
use Argtyper202511\Rector\Privatization\NodeManipulator\VisibilityManipulator;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Rector\ValueObject\MethodName;
use Argtyper202511\Rector\ValueObject\Visibility;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://wiki.php.net/rfc/final_promotion
 *
 * @see \Rector\Tests\DowngradePhp85\Rector\Class_\DowngradeFinalPropertyPromotionRector\DowngradeFinalPropertyPromotionRectorTest
 */
final class DowngradeFinalPropertyPromotionRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Privatization\NodeManipulator\VisibilityManipulator
     */
    private $visibilityManipulator;
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
     * @var string
     */
    private const TAGNAME = 'final';
    public function __construct(VisibilityManipulator $visibilityManipulator, DocBlockUpdater $docBlockUpdater, PhpDocInfoFactory $phpDocInfoFactory)
    {
        $this->visibilityManipulator = $visibilityManipulator;
        $this->docBlockUpdater = $docBlockUpdater;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change constructor final property promotion to @final tag', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function __construct(
        final public string $id
    ){}
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function __construct(
        /** @final */
        public string $id
    ) {}
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
    public function refactor(Node $node) : ?ClassMethod
    {
        if (!$this->isName($node, MethodName::CONSTRUCT)) {
            return null;
        }
        $hasChanged = \false;
        foreach ($node->params as $param) {
            if (!$param->isPromoted()) {
                continue;
            }
            if (!$this->visibilityManipulator->hasVisibility($param, Visibility::FINAL)) {
                continue;
            }
            $hasChanged = \true;
            $this->visibilityManipulator->makeNonFinal($param);
            if (!$param->isPromoted()) {
                $this->visibilityManipulator->makePublic($param);
            }
            $this->addPhpDocTag($param);
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    /**
     * @param \PhpParser\Builder\Property|\PhpParser\Node\Param $node
     */
    private function addPhpDocTag($node) : void
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        if ($phpDocInfo->hasByName(self::TAGNAME)) {
            return;
        }
        $phpDocInfo->addPhpDocTagNode(new PhpDocTagNode('@' . self::TAGNAME, new GenericTagValueNode('')));
        $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($node);
    }
}
