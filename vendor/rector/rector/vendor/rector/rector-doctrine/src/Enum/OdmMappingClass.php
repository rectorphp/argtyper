<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Doctrine\Enum;

final class OdmMappingClass
{
    /**
     * @var string
     */
    public const DOCUMENT = 'Argtyper202511\Doctrine\ODM\MongoDB\Mapping\Annotations\Document';
    /**
     * @var string
     */
    public const REFERENCE_MANY = 'Argtyper202511\Doctrine\ODM\MongoDB\Mapping\Annotations\ReferenceMany';
    /**
     * @var string
     */
    public const REFERENCE_ONE = 'Argtyper202511\Doctrine\ODM\MongoDB\Mapping\Annotations\ReferenceOne';
    /**
     * @var string
     */
    public const EMBED_MANY = 'Argtyper202511\Doctrine\ODM\MongoDB\Mapping\Annotations\EmbedMany';
    /**
     * @var string
     */
    public const EMBED_ONE = 'Argtyper202511\Doctrine\ODM\MongoDB\Mapping\Annotations\EmbedOne';
}
