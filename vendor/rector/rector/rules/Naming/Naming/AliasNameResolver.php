<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Naming\Naming;

use Argtyper202511\PhpParser\Node\Identifier;
use Argtyper202511\PhpParser\Node\Name\FullyQualified;
use Argtyper202511\PhpParser\Node\Stmt\GroupUse;
use Argtyper202511\PhpParser\Node\Stmt\Use_;
final class AliasNameResolver
{
    /**
     * @readonly
     * @var \Rector\Naming\Naming\UseImportsResolver
     */
    private $useImportsResolver;
    public function __construct(\Argtyper202511\Rector\Naming\Naming\UseImportsResolver $useImportsResolver)
    {
        $this->useImportsResolver = $useImportsResolver;
    }
    /**
     * @param array<Use_|GroupUse> $uses
     */
    public function resolveByName(FullyQualified $fullyQualified, array $uses): ?string
    {
        $nameString = $fullyQualified->toString();
        foreach ($uses as $use) {
            $prefix = $this->useImportsResolver->resolvePrefix($use);
            foreach ($use->uses as $useUse) {
                if (!$useUse->alias instanceof Identifier) {
                    continue;
                }
                $fullyQualified = $prefix . $useUse->name->toString();
                if ($fullyQualified !== $nameString) {
                    continue;
                }
                return (string) $useUse->getAlias();
            }
        }
        return null;
    }
}
