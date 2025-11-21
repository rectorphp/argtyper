<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\StaticTypeMapper\ValueObject\Type;

use Argtyper202511\RectorPrefix202511\Nette\Utils\Strings;
use Argtyper202511\PhpParser\Node\Name;
use Argtyper202511\PhpParser\Node\Stmt\Use_;
use Argtyper202511\PhpParser\Node\UseItem;
use Argtyper202511\PHPStan\Type\ObjectType;
use Argtyper202511\Rector\NodeTypeResolver\Node\AttributeKey;
/**
 * @api
 */
final class FullyQualifiedObjectType extends ObjectType
{
    public function getShortNameType(): \Argtyper202511\Rector\StaticTypeMapper\ValueObject\Type\ShortenedObjectType
    {
        return new \Argtyper202511\Rector\StaticTypeMapper\ValueObject\Type\ShortenedObjectType($this->getShortName(), $this->getClassName());
    }
    /**
     * @param \Rector\StaticTypeMapper\ValueObject\Type\AliasedObjectType|$this $comparedObjectType
     */
    public function areShortNamesEqual($comparedObjectType): bool
    {
        return $this->getShortName() === $comparedObjectType->getShortName();
    }
    public function getShortName(): string
    {
        $className = $this->getClassName();
        if (strpos($className, '\\') === \false) {
            return $className;
        }
        return (string) Strings::after($className, '\\', -1);
    }
    public function getShortNameNode(): Name
    {
        $name = new Name($this->getShortName());
        // keep original to avoid loss on while importing
        $name->setAttribute(AttributeKey::NAMESPACED_NAME, $this->getClassName());
        return $name;
    }
    /**
     * @param Use_::TYPE_* $useType
     */
    public function getUseNode(int $useType): Use_
    {
        $name = new Name($this->getClassName());
        $useItem = new UseItem($name);
        $use = new Use_([$useItem]);
        $use->type = $useType;
        return $use;
    }
    public function getShortNameLowered(): string
    {
        return strtolower($this->getShortName());
    }
}
