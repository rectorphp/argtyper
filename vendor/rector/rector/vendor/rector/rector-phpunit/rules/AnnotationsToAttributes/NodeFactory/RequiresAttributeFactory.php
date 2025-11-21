<?php

declare (strict_types=1);
namespace Rector\PHPUnit\AnnotationsToAttributes\NodeFactory;

use Argtyper202511\PhpParser\Node\AttributeGroup;
use Rector\PhpAttribute\NodeFactory\PhpAttributeGroupFactory;
final class RequiresAttributeFactory
{
    /**
     * @readonly
     * @var \Rector\PhpAttribute\NodeFactory\PhpAttributeGroupFactory
     */
    private $phpAttributeGroupFactory;
    public function __construct(PhpAttributeGroupFactory $phpAttributeGroupFactory)
    {
        $this->phpAttributeGroupFactory = $phpAttributeGroupFactory;
    }
    public function create(string $annotationValue): ?AttributeGroup
    {
        $annotationValues = explode(' ', $annotationValue, 2);
        $type = array_shift($annotationValues);
        $attributeValue = array_shift($annotationValues);
        switch ($type) {
            case 'PHP':
                $attributeClass = 'Argtyper202511\PHPUnit\Framework\Attributes\RequiresPhp';
                // only version is used, we need to prefix with >=
                if (is_string($attributeValue) && is_numeric($attributeValue[0])) {
                    $attributeValue = '>= ' . $attributeValue;
                }
                $attributeValue = [$attributeValue];
                break;
            case 'PHPUnit':
                $attributeClass = 'Argtyper202511\PHPUnit\Framework\Attributes\RequiresPhpunit';
                // only version is used, we need to prefix with >=
                if (is_string($attributeValue) && is_numeric($attributeValue[0])) {
                    $attributeValue = '>= ' . $attributeValue;
                }
                $attributeValue = [$attributeValue];
                break;
            case 'OS':
                $attributeClass = 'Argtyper202511\PHPUnit\Framework\Attributes\RequiresOperatingSystem';
                $attributeValue = [$attributeValue];
                break;
            case 'OSFAMILY':
                $attributeClass = 'Argtyper202511\PHPUnit\Framework\Attributes\RequiresOperatingSystemFamily';
                $attributeValue = [$attributeValue];
                break;
            case 'function':
                if (strpos((string) $attributeValue, '::') !== \false) {
                    $attributeClass = 'Argtyper202511\PHPUnit\Framework\Attributes\RequiresMethod';
                    $attributeValue = explode('::', (string) $attributeValue);
                    $attributeValue[0] .= '::class';
                } else {
                    $attributeClass = 'Argtyper202511\PHPUnit\Framework\Attributes\RequiresFunction';
                    $attributeValue = [$attributeValue];
                }
                break;
            case 'extension':
                $attributeClass = 'Argtyper202511\PHPUnit\Framework\Attributes\RequiresPhpExtension';
                $attributeValue = explode(' ', (string) $attributeValue, 2);
                break;
            case 'setting':
                $attributeClass = 'Argtyper202511\PHPUnit\Framework\Attributes\RequiresSetting';
                $attributeValue = explode(' ', (string) $attributeValue, 2);
                break;
            default:
                return null;
        }
        return $this->phpAttributeGroupFactory->createFromClassWithItems($attributeClass, array_merge($attributeValue));
    }
}
