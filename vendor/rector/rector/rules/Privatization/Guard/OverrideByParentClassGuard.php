<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Privatization\Guard;

use Argtyper202511\PhpParser\Node\Name\FullyQualified;
use Argtyper202511\PhpParser\Node\Stmt\Class_;
use Argtyper202511\PHPStan\Reflection\ReflectionProvider;
/**
 * Verify whether Class_'s method or property allowed to be overridden by verify class parent or implements exists
 */
final class OverrideByParentClassGuard
{
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    public function __construct(ReflectionProvider $reflectionProvider)
    {
        $this->reflectionProvider = $reflectionProvider;
    }
    public function isLegal(Class_ $class) : bool
    {
        if ($class->extends instanceof FullyQualified && !$this->reflectionProvider->hasClass($class->extends->toString())) {
            return \false;
        }
        foreach ($class->implements as $implement) {
            if (!$this->reflectionProvider->hasClass($implement->toString())) {
                return \false;
            }
        }
        return \true;
    }
}
