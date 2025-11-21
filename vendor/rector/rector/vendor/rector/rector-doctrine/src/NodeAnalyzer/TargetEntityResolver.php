<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Doctrine\NodeAnalyzer;

use Argtyper202511\PhpParser\Node\Attribute;
use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Expr\ClassConstFetch;
use Argtyper202511\PhpParser\Node\Identifier;
use Argtyper202511\PhpParser\Node\Scalar\String_;
use Argtyper202511\PHPStan\Reflection\ReflectionProvider;
use Argtyper202511\Rector\Doctrine\CodeQuality\Enum\DocumentMappingKey;
use Argtyper202511\Rector\Doctrine\CodeQuality\Enum\EntityMappingKey;
use Argtyper202511\Rector\Exception\NotImplementedYetException;
use Argtyper202511\Rector\NodeNameResolver\NodeNameResolver;
final class TargetEntityResolver
{
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    public function __construct(NodeNameResolver $nodeNameResolver, ReflectionProvider $reflectionProvider)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->reflectionProvider = $reflectionProvider;
    }
    public function resolveFromAttribute(Attribute $attribute): ?string
    {
        foreach ($attribute->args as $arg) {
            if (!$arg->name instanceof Identifier) {
                continue;
            }
            if (!in_array($arg->name->toString(), [EntityMappingKey::TARGET_ENTITY, DocumentMappingKey::TARGET_DOCUMENT], \true)) {
                continue;
            }
            return $this->resolveFromExpr($arg->value);
        }
        return null;
    }
    public function resolveFromExpr(Expr $targetEntityExpr): ?string
    {
        if ($targetEntityExpr instanceof ClassConstFetch) {
            $targetEntity = (string) $this->nodeNameResolver->getName($targetEntityExpr->class);
            if (!$this->reflectionProvider->hasClass($targetEntity)) {
                return null;
            }
            return $targetEntity;
        }
        if ($targetEntityExpr instanceof String_) {
            $targetEntity = $targetEntityExpr->value;
            if (!$this->reflectionProvider->hasClass($targetEntity)) {
                return null;
            }
            return $targetEntity;
        }
        $errorMessage = sprintf('Add support for "%s" targetEntity in "%s"', get_class($targetEntityExpr), self::class);
        throw new NotImplementedYetException($errorMessage);
    }
}
