<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\BetterPhpDocParser\ValueObject;

use Argtyper202511\PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use Argtyper202511\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use Argtyper202511\PHPStan\PhpDocParser\Ast\PhpDoc\PropertyTagValueNode;
use Argtyper202511\PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use Argtyper202511\PHPStan\PhpDocParser\Ast\PhpDoc\TemplateTagValueNode;
use Argtyper202511\PHPStan\PhpDocParser\Ast\PhpDoc\ThrowsTagValueNode;
use Argtyper202511\PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use Argtyper202511\Rector\Enum\ClassName;
final class NodeTypes
{
    /**
     * @var array<class-string<PhpDocTagValueNode>>
     */
    public const TYPE_AWARE_NODES = [VarTagValueNode::class, ParamTagValueNode::class, ReturnTagValueNode::class, ThrowsTagValueNode::class, PropertyTagValueNode::class, TemplateTagValueNode::class];
    /**
     * @var string[]
     */
    public const TYPE_AWARE_DOCTRINE_ANNOTATION_CLASSES = [ClassName::JMS_TYPE, 'Argtyper202511\\Doctrine\\ORM\\Mapping\\OneToMany', 'Argtyper202511\\Symfony\\Component\\Validator\\Constraints\\Choice', 'Argtyper202511\\Symfony\\Component\\Validator\\Constraints\\Email', 'Argtyper202511\\Symfony\\Component\\Validator\\Constraints\\Range'];
}
