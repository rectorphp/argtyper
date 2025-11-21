<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\TypeDeclaration\NodeTypeAnalyzer;

use Argtyper202511\PhpParser\Node\ComplexType;
use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Identifier;
use Argtyper202511\PhpParser\Node\Name;
use Argtyper202511\PhpParser\Node\Stmt\Property;
use Argtyper202511\PHPStan\Type\TypeCombinator;
use Argtyper202511\PHPStan\Type\UnionType;
use Argtyper202511\PHPStan\Type\VerbosityLevel;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use Argtyper202511\Rector\Php\PhpVersionProvider;
use Argtyper202511\Rector\PhpParser\Node\NodeFactory;
use Argtyper202511\Rector\ValueObject\PhpVersionFeature;
final class PropertyTypeDecorator
{
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger
     */
    private $phpDocTypeChanger;
    /**
     * @readonly
     * @var \Rector\Php\PhpVersionProvider
     */
    private $phpVersionProvider;
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\NodeFactory
     */
    private $nodeFactory;
    public function __construct(PhpDocTypeChanger $phpDocTypeChanger, PhpVersionProvider $phpVersionProvider, NodeFactory $nodeFactory)
    {
        $this->phpDocTypeChanger = $phpDocTypeChanger;
        $this->phpVersionProvider = $phpVersionProvider;
        $this->nodeFactory = $nodeFactory;
    }
    /**
     * @param \PhpParser\Node\Name|\PhpParser\Node\ComplexType|\PhpParser\Node\Identifier $typeNode
     */
    public function decoratePropertyUnionType(UnionType $unionType, $typeNode, Property $property, PhpDocInfo $phpDocInfo, bool $changeVarTypeFallback = \true) : void
    {
        if (!TypeCombinator::containsNull($unionType)) {
            if ($this->phpVersionProvider->isAtLeastPhpVersion(PhpVersionFeature::UNION_TYPES)) {
                $property->type = $typeNode;
                return;
            }
            if ($changeVarTypeFallback) {
                $this->phpDocTypeChanger->changeVarType($property, $phpDocInfo, $unionType);
            }
            return;
        }
        $property->type = $typeNode;
        $propertyProperty = $property->props[0];
        // add null default
        if (!$propertyProperty->default instanceof Expr) {
            $propertyProperty->default = $this->nodeFactory->createNull();
        }
        // has array with defined type? add docs
        if (!$this->isDocBlockRequired($unionType)) {
            return;
        }
        if (!$changeVarTypeFallback) {
            return;
        }
        $this->phpDocTypeChanger->changeVarType($property, $phpDocInfo, $unionType);
    }
    private function isDocBlockRequired(UnionType $unionType) : bool
    {
        foreach ($unionType->getTypes() as $unionedType) {
            if ($unionedType->isArray()->yes()) {
                $describedArray = $unionedType->describe(VerbosityLevel::value());
                if ($describedArray !== 'array') {
                    return \true;
                }
            }
        }
        return \false;
    }
}
