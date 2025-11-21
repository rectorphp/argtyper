<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\DowngradePhp80\Rector\Instanceof_;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp;
use Argtyper202511\PhpParser\Node\Expr\Instanceof_;
use Argtyper202511\Rector\DowngradePhp81\NodeManipulator\ObjectToResourceReturn;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://www.php.net/manual/en/migration80.incompatible.php#migration80.incompatible.resource2object
 *
 * @see \Rector\Tests\DowngradePhp80\Rector\Instanceof_\DowngradePhp80ResourceReturnToObjectRector\DowngradePhp80ResourceReturnToObjectRectorTest
 */
final class DowngradePhp80ResourceReturnToObjectRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\DowngradePhp81\NodeManipulator\ObjectToResourceReturn
     */
    private $objectToResourceReturn;
    /**
     * @var string[]
     */
    private const COLLECTION_OBJECT_TO_RESOURCE = [
        // curl
        'CurlHandle',
        'CurlMultiHandle',
        'CurlShareHandle',
        // socket
        'Socket',
        // GD
        'GdImage',
        // XMLWriter
        'XMLWriter',
        // XMLParser
        'XMLParser',
        // Broker
        'EnchantBroker',
        'EnchantDictionary',
        // OpenSSL
        'OpenSSLCertificate',
        'OpenSSLCertificateSigningRequest',
        // Shmop
        'Shmop',
        // MessageQueue
        'SysvMessageQueue',
        'SysvSemaphore',
        'SysvSharedMemory',
        // Inflate Deflate
        'InflateContext',
        'DeflateContext',
    ];
    public function __construct(ObjectToResourceReturn $objectToResourceReturn)
    {
        $this->objectToResourceReturn = $objectToResourceReturn;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('change instanceof Object to is_resource', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run($obj)
    {
        $obj instanceof \CurlHandle;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run($obj)
    {
        is_resource($obj) || $obj instanceof \CurlHandle;
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [BinaryOp::class, Instanceof_::class];
    }
    /**
     * @param BinaryOp|Instanceof_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        return $this->objectToResourceReturn->refactor($node, self::COLLECTION_OBJECT_TO_RESOURCE);
    }
}
