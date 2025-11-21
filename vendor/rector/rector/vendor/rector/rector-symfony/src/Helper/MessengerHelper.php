<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Symfony\Helper;

use Argtyper202511\PhpParser\Node\Attribute;
use Argtyper202511\PhpParser\Node\AttributeGroup;
use Argtyper202511\PhpParser\Node\Name\FullyQualified;
use Argtyper202511\PhpParser\Node\Stmt\Class_;
use Argtyper202511\PhpParser\Node\Stmt\ClassMethod;
use Argtyper202511\Rector\PhpAttribute\AttributeArrayNameInliner;
use Argtyper202511\Rector\PhpAttribute\NodeFactory\PhpAttributeGroupFactory;
use Argtyper202511\Rector\Symfony\DataProvider\ServiceMapProvider;
use Argtyper202511\Rector\Symfony\ValueObject\ServiceDefinition;
final class MessengerHelper
{
    /**
     * @readonly
     * @var \Rector\PhpAttribute\NodeFactory\PhpAttributeGroupFactory
     */
    private $phpAttributeGroupFactory;
    /**
     * @readonly
     * @var \Rector\PhpAttribute\AttributeArrayNameInliner
     */
    private $attributeArrayNameInliner;
    /**
     * @readonly
     * @var \Rector\Symfony\DataProvider\ServiceMapProvider
     */
    private $serviceMapProvider;
    public const MESSAGE_HANDLER_INTERFACE = 'Argtyper202511\\Symfony\\Component\\Messenger\\Handler\\MessageHandlerInterface';
    public const MESSAGE_SUBSCRIBER_INTERFACE = 'Argtyper202511\\Symfony\\Component\\Messenger\\Handler\\MessageSubscriberInterface';
    public const AS_MESSAGE_HANDLER_ATTRIBUTE = 'Argtyper202511\\Symfony\\Component\\Messenger\\Attribute\\AsMessageHandler';
    /**
     * @var string
     */
    private $messengerTagName = 'messenger.message_handler';
    /**
     * @var ServiceDefinition[]
     */
    private $handlersFromServices = [];
    public function __construct(PhpAttributeGroupFactory $phpAttributeGroupFactory, AttributeArrayNameInliner $attributeArrayNameInliner, ServiceMapProvider $serviceMapProvider)
    {
        $this->phpAttributeGroupFactory = $phpAttributeGroupFactory;
        $this->attributeArrayNameInliner = $attributeArrayNameInliner;
        $this->serviceMapProvider = $serviceMapProvider;
    }
    /**
     * @return array<string, mixed>
     */
    public function extractOptionsFromServiceDefinition(ServiceDefinition $serviceDefinition) : array
    {
        $options = [];
        foreach ($serviceDefinition->getTags() as $tag) {
            if ($this->messengerTagName === $tag->getName()) {
                $options = $tag->getData();
            }
        }
        if ($options['from_transport']) {
            $options['fromTransport'] = $options['from_transport'];
            unset($options['from_transport']);
        }
        return $options;
    }
    /**
     * @return ServiceDefinition[]
     */
    public function getHandlersFromServices() : array
    {
        if ($this->handlersFromServices !== []) {
            return $this->handlersFromServices;
        }
        $serviceMap = $this->serviceMapProvider->provide();
        $this->handlersFromServices = $serviceMap->getServicesByTag($this->messengerTagName);
        return $this->handlersFromServices;
    }
    /**
     * @param array<string, mixed> $options
     * @param \PhpParser\Node\Stmt\Class_|\PhpParser\Node\Stmt\ClassMethod $node
     * @return \PhpParser\Node\Stmt\Class_|\PhpParser\Node\Stmt\ClassMethod
     */
    public function addAttribute($node, array $options = [])
    {
        $args = $this->phpAttributeGroupFactory->createArgsFromItems($options, self::AS_MESSAGE_HANDLER_ATTRIBUTE);
        $args = $this->attributeArrayNameInliner->inlineArrayToArgs($args);
        $node->attrGroups = \array_merge($node->attrGroups, [new AttributeGroup([new Attribute(new FullyQualified(self::AS_MESSAGE_HANDLER_ATTRIBUTE), $args)])]);
        return $node;
    }
}
