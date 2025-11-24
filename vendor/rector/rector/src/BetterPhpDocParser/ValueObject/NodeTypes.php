<?php

declare (strict_types=1);
namespace Rector\BetterPhpDocParser\ValueObject;

use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PropertyTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\TemplateTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\ThrowsTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use Rector\Enum\ClassName;
final class NodeTypes
{
    /**
     * @var array<class-string<PhpDocTagValueNode>>
     */
    public const TYPE_AWARE_NODES = [VarTagValueNode::class, ParamTagValueNode::class, ReturnTagValueNode::class, ThrowsTagValueNode::class, PropertyTagValueNode::class, TemplateTagValueNode::class];
    /**
     * @var string[]
     */
    public const TYPE_AWARE_DOCTRINE_ANNOTATION_CLASSES = [ClassName::JMS_TYPE, 'Argtyper202511\Doctrine\ORM\Mapping\OneToMany', 'Argtyper202511\Symfony\Component\Validator\Constraints\Choice', 'Argtyper202511\Symfony\Component\Validator\Constraints\Email', 'Argtyper202511\Symfony\Component\Validator\Constraints\Range'];
}
