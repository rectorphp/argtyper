<?php

declare(strict_types=1);

namespace Rector\ArgTyper\PHPStan\Collectors;

use PhpParser\Node;
use PhpParser\Node\Expr\CallLike;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\NullsafeMethodCall;
use PhpParser\Node\Identifier;
use PHPStan\Analyser\Scope;
use PHPStan\Collectors\Collector;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\ShouldNotHappenException;
use PHPStan\Type\ArrayType;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\ErrorType;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeWithClassName;
use PHPStan\Type\UnionType;
use PHPStan\Type\VerbosityLevel;
use Rector\ArgTyper\Configuration\ProjectAutoloadGuard;
use Rector\ArgTyper\PHPStan\CallLikeClassReflectionResolver;
use Rector\ArgTyper\PHPStan\TypeMapper;

/**
 * @implements Collector<CallLike, array<array{0: string, 1: string, 2: string, 3: string}>>
 */
final class CallLikeArgTypeCollector implements Collector
{
    private CallLikeClassReflectionResolver $callLikeClassReflectionResolver;

    public function __construct(
        private ReflectionProvider $reflectionProvider
    ) {
        $this->callLikeClassReflectionResolver = new CallLikeClassReflectionResolver($reflectionProvider);
    }

    public function getNodeType(): string
    {
        return CallLike::class;
    }

    /**
     * @param CallLike $node
     */
    public function processNode(Node $node, Scope $scope): ?array
    {
        // nothing magic here
        if ($node->isFirstClassCallable()) {
            return null;
        }

        // 1.
        if ($node instanceof MethodCall || $node instanceof NullsafeMethodCall) {
        }

        // 2.
        if ($node instanceof Node\Expr\New_) {

        }

        if (! $node->name instanceof Identifier) {
            return null;
        }

        // we need at least some args
        if ($node->getArgs() === []) {
            return null;
        }

        $methodCallName = $node->name->toString();
        $callerType = $scope->getType($node->var);

        // @todo check if this can be less strict, e.g. for nullable etc.
        if (! $callerType->isObject()->yes()) {
            return null;
        }

        ProjectAutoloadGuard::ensureProjectAutoloadFileIsLoaded($callerType);

        $classNameTypes = [];

        $objectClassReflections = $callerType->getObjectClassReflections();
        foreach ($objectClassReflections as $objectClassReflection) {
            if (! $objectClassReflection->hasMethod($methodCallName)) {
                continue;
            }

            if ($this->shouldSkipClassReflection($objectClassReflection)) {
                continue;
            }

            $className = $objectClassReflection->getName();
            foreach ($node->getArgs() as $key => $arg) {
                // handle later, now work with order
                if ($arg->name instanceof Identifier) {
                    continue;
                }

                $argType = $scope->getType($arg->value);
                if ($this->shouldSkipType($argType)) {
                    continue;
                }

                if ($argType instanceof TypeWithClassName) {
                    $type = 'object:' . $argType->getClassName();
                } else {
                    $type = TypeMapper::mapConstantToGenericTypes($argType);
                    if ($type instanceof ArrayType || $type instanceof ConstantArrayType) {
                        $type = $type->describe(VerbosityLevel::typeOnly());
                    } else {
                        $type = $type::class;
                    }
                }

                $classNameTypes[] = [$className, $methodCallName, $key, $type];
            }
        }

        // avoid empty array processing in the rule
        if ($classNameTypes === []) {
            return null;
        }

        return $classNameTypes;
    }

    private function ensureProjectAutoloadFileIsLoaded(\PHPStan\Type\Type $callerType): void
    {
        if (! $callerType instanceof ObjectType) {
            return;
        }

        // call reflection is loaded properly
        if ($callerType->getClassReflection() instanceof ClassReflection) {
            return;
        }

        throw new ShouldNotHappenException(sprintf(
            'Class reflection for "%s" class not found. Make sure you included the project autoload. --autoload-file=project/vendor/autoload.php',
            $callerType->getClassName()
        ));
    }

    private function shouldSkipClassReflection(ClassReflection $classReflection): bool
    {
        if ($classReflection->isInternal()) {
            return true;
        }

        $fileName = $classReflection->getFileName();

        // most likely internal or magic
        if ($fileName === null) {
            return true;
        }

        return str_contains($fileName, '/vendor');
    }

    private function shouldSkipType(Type $type): bool
    {
        // unable to move to json for now, handle later
        if ($type instanceof ErrorType) {
            return true;
        }

        if ($type instanceof MixedType) {
            return true;
        }

        if ($type instanceof UnionType) {
            return true;
        }

        return $type instanceof IntersectionType;
    }
}
