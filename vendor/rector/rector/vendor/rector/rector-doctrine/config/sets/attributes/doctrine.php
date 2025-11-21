<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\Php80\Rector\Class_\AnnotationToAttributeRector;
use Argtyper202511\Rector\Php80\Rector\Property\NestedAnnotationToAttributeRector;
use Argtyper202511\Rector\Php80\ValueObject\AnnotationPropertyToAttributeClass;
use Argtyper202511\Rector\Php80\ValueObject\AnnotationToAttribute;
use Argtyper202511\Rector\Php80\ValueObject\NestedAnnotationToAttribute;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->ruleWithConfiguration(NestedAnnotationToAttributeRector::class, [
        /** @see https://www.doctrine-project.org/projects/doctrine-orm/en/2.13/reference/attributes-reference.html#joincolumn-inversejoincolumn */
        new NestedAnnotationToAttribute('Argtyper202511\Doctrine\ORM\Mapping\JoinTable', [new AnnotationPropertyToAttributeClass('Argtyper202511\Doctrine\ORM\Mapping\JoinColumn', 'joinColumns'), new AnnotationPropertyToAttributeClass('Argtyper202511\Doctrine\ORM\Mapping\InverseJoinColumn', 'inverseJoinColumns', \true)]),
        /** @see https://www.doctrine-project.org/projects/doctrine-orm/en/latest/reference/annotations-reference.html#joincolumns */
        new NestedAnnotationToAttribute('Argtyper202511\Doctrine\ORM\Mapping\JoinColumns', [new AnnotationPropertyToAttributeClass('Argtyper202511\Doctrine\ORM\Mapping\JoinColumn')], \true),
        new NestedAnnotationToAttribute('Argtyper202511\Doctrine\ORM\Mapping\Table', [new AnnotationPropertyToAttributeClass('Argtyper202511\Doctrine\ORM\Mapping\Index', 'indexes', \true), new AnnotationPropertyToAttributeClass('Argtyper202511\Doctrine\ORM\Mapping\UniqueConstraint', 'uniqueConstraints', \true)]),
    ]);
    $rectorConfig->ruleWithConfiguration(AnnotationToAttributeRector::class, [
        // class
        new AnnotationToAttribute('Argtyper202511\Doctrine\ORM\Mapping\Entity', null, ['repositoryClass']),
        new AnnotationToAttribute('Argtyper202511\Doctrine\ORM\Mapping\Column'),
        new AnnotationToAttribute('Argtyper202511\Doctrine\ORM\Mapping\UniqueConstraint'),
        // id
        new AnnotationToAttribute('Argtyper202511\Doctrine\ORM\Mapping\Id'),
        new AnnotationToAttribute('Argtyper202511\Doctrine\ORM\Mapping\GeneratedValue'),
        new AnnotationToAttribute('Argtyper202511\Doctrine\ORM\Mapping\SequenceGenerator'),
        new AnnotationToAttribute('Argtyper202511\Doctrine\ORM\Mapping\Index'),
        new AnnotationToAttribute('Argtyper202511\Doctrine\ORM\Mapping\CustomIdGenerator', null, ['class']),
        // relations
        new AnnotationToAttribute('Argtyper202511\Doctrine\ORM\Mapping\OneToOne', null, ['targetEntity']),
        new AnnotationToAttribute('Argtyper202511\Doctrine\ORM\Mapping\OneToMany', null, ['targetEntity']),
        new AnnotationToAttribute('Argtyper202511\Doctrine\ORM\Mapping\ManyToMany', null, ['targetEntity']),
        new AnnotationToAttribute('Argtyper202511\Doctrine\ORM\Mapping\JoinTable'),
        new AnnotationToAttribute('Argtyper202511\Doctrine\ORM\Mapping\ManyToOne', null, ['targetEntity']),
        new AnnotationToAttribute('Argtyper202511\Doctrine\ORM\Mapping\OrderBy'),
        new AnnotationToAttribute('Argtyper202511\Doctrine\ORM\Mapping\JoinColumn'),
        // embed
        new AnnotationToAttribute('Argtyper202511\Doctrine\ORM\Mapping\Embeddable'),
        new AnnotationToAttribute('Argtyper202511\Doctrine\ORM\Mapping\Embedded', null, ['class']),
        // inheritance
        new AnnotationToAttribute('Argtyper202511\Doctrine\ORM\Mapping\MappedSuperclass', null, ['repositoryClass']),
        new AnnotationToAttribute('Argtyper202511\Doctrine\ORM\Mapping\InheritanceType'),
        new AnnotationToAttribute('Argtyper202511\Doctrine\ORM\Mapping\DiscriminatorColumn'),
        new AnnotationToAttribute('Argtyper202511\Doctrine\ORM\Mapping\DiscriminatorMap'),
        new AnnotationToAttribute('Argtyper202511\Doctrine\ORM\Mapping\Version'),
        new AnnotationToAttribute('Argtyper202511\Doctrine\ORM\Mapping\ChangeTrackingPolicy'),
        // events
        new AnnotationToAttribute('Argtyper202511\Doctrine\ORM\Mapping\HasLifecycleCallbacks'),
        new AnnotationToAttribute('Argtyper202511\Doctrine\ORM\Mapping\PostLoad'),
        new AnnotationToAttribute('Argtyper202511\Doctrine\ORM\Mapping\PostPersist'),
        new AnnotationToAttribute('Argtyper202511\Doctrine\ORM\Mapping\PostRemove'),
        new AnnotationToAttribute('Argtyper202511\Doctrine\ORM\Mapping\PostUpdate'),
        new AnnotationToAttribute('Argtyper202511\Doctrine\ORM\Mapping\PreFlush'),
        new AnnotationToAttribute('Argtyper202511\Doctrine\ORM\Mapping\PrePersist'),
        new AnnotationToAttribute('Argtyper202511\Doctrine\ORM\Mapping\PreRemove'),
        new AnnotationToAttribute('Argtyper202511\Doctrine\ORM\Mapping\PreUpdate'),
        new AnnotationToAttribute('Argtyper202511\Doctrine\ORM\Mapping\Cache'),
        new AnnotationToAttribute('Argtyper202511\Doctrine\ORM\Mapping\EntityListeners'),
        // Overrides
        new AnnotationToAttribute('Argtyper202511\Doctrine\ORM\Mapping\AssociationOverrides'),
        new AnnotationToAttribute('Argtyper202511\Doctrine\ORM\Mapping\AssociationOverride'),
        new AnnotationToAttribute('Argtyper202511\Doctrine\ORM\Mapping\AttributeOverrides'),
        new AnnotationToAttribute('Argtyper202511\Doctrine\ORM\Mapping\AttributeOverride'),
    ]);
};
