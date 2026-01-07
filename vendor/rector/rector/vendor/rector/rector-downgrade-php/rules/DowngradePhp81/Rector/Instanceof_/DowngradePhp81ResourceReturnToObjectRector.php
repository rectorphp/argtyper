<?php

declare (strict_types=1);
namespace Rector\DowngradePhp81\Rector\Instanceof_;

use finfo;
use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\Instanceof_;
use Rector\DowngradePhp81\NodeManipulator\ObjectToResourceReturn;
use Rector\Rector\AbstractRector;
use Argtyper202601\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202601\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://www.php.net/manual/en/migration81.incompatible.php#migration81.incompatible.resource2object
 *
 * @see \Rector\Tests\DowngradePhp81\Rector\Instanceof_\DowngradePhp81ResourceReturnToObjectRector\DowngradePhp81ResourceReturnToObjectRectorTest
 */
final class DowngradePhp81ResourceReturnToObjectRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\DowngradePhp81\NodeManipulator\ObjectToResourceReturn
     */
    private $objectToResourceReturn;
    /**
     * @var string[]|class-string<finfo>[]
     */
    private const COLLECTION_OBJECT_TO_RESOURCE = [
        // finfo
        'finfo',
        // ftp
        'Argtyper202601\FTP\Connection',
        // imap_open
        'Argtyper202601\IMAP\Connection',
        // pspell
        'Argtyper202601\PSpell\Config',
        'Argtyper202601\PSpell\Dictionary',
        // ldap
        'Argtyper202601\LDAP\Connection',
        'Argtyper202601\LDAP\Result',
        'Argtyper202601\LDAP\ResultEntry',
        // psql
        'Argtyper202601\PgSql\Connection',
        'Argtyper202601\PgSql\Result',
        'Argtyper202601\PgSql\Lob',
    ];
    public function __construct(ObjectToResourceReturn $objectToResourceReturn)
    {
        $this->objectToResourceReturn = $objectToResourceReturn;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('change instanceof Object to is_resource', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run($obj)
    {
        $obj instanceof \finfo;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run($obj)
    {
        is_resource($obj) || $obj instanceof \finfo;
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [BinaryOp::class, Instanceof_::class];
    }
    /**
     * @param BinaryOp|Instanceof_ $node
     */
    public function refactor(Node $node): ?Node
    {
        return $this->objectToResourceReturn->refactor($node, self::COLLECTION_OBJECT_TO_RESOURCE);
    }
}
