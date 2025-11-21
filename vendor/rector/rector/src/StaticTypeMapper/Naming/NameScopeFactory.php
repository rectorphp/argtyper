<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\StaticTypeMapper\Naming;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Stmt\GroupUse;
use Argtyper202511\PhpParser\Node\Stmt\Use_;
use Argtyper202511\PhpParser\Node\UseItem;
use Argtyper202511\PHPStan\Analyser\NameScope;
use Argtyper202511\PHPStan\Analyser\Scope;
use Argtyper202511\PHPStan\Reflection\ClassReflection;
use Argtyper202511\Rector\Naming\Naming\UseImportsResolver;
use Argtyper202511\Rector\NodeTypeResolver\Node\AttributeKey;
/**
 * @see https://github.com/phpstan/phpstan-src/blob/8376548f76e2c845ae047e3010e873015b796818/src/Analyser/NameScope.php#L32
 */
final class NameScopeFactory
{
    /**
     * @readonly
     * @var \Rector\Naming\Naming\UseImportsResolver
     */
    private $useImportsResolver;
    public function __construct(UseImportsResolver $useImportsResolver)
    {
        $this->useImportsResolver = $useImportsResolver;
    }
    public function createNameScopeFromNodeWithoutTemplateTypes(Node $node): NameScope
    {
        $scope = $node->getAttribute(AttributeKey::SCOPE);
        if ($scope instanceof Scope) {
            $namespace = $scope->getNamespace();
            $classReflection = $scope->getClassReflection();
            $className = $classReflection instanceof ClassReflection ? $classReflection->getName() : null;
        } else {
            $namespace = null;
            $className = null;
        }
        $uses = $this->useImportsResolver->resolve();
        $usesAliasesToNames = $this->resolveUseNamesByAlias($uses);
        return new NameScope($namespace, $usesAliasesToNames, $className);
    }
    /**
     * @param array<Use_|GroupUse> $useNodes
     * @return array<string, string>
     */
    private function resolveUseNamesByAlias(array $useNodes): array
    {
        $useNamesByAlias = [];
        foreach ($useNodes as $useNode) {
            $prefix = $this->useImportsResolver->resolvePrefix($useNode);
            foreach ($useNode->uses as $useUse) {
                /** @var UseItem $useUse */
                $aliasName = $useUse->getAlias()->name;
                // uses must be lowercase, as PHPStan lowercases it
                $lowercasedAliasName = strtolower($aliasName);
                $useNamesByAlias[$lowercasedAliasName] = $prefix . $useUse->name->toString();
            }
        }
        return $useNamesByAlias;
    }
}
