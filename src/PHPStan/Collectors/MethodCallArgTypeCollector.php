<?php

declare(strict_types=1);

namespace Rector\ArgTyper\PHPStan\Collectors;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PHPStan\Analyser\Scope;
use PHPStan\Collectors\Collector;
use PHPStan\Reflection\ClassReflection;
use PHPStan\ShouldNotHappenException;
use PHPStan\Type\ArrayType;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\TypeWithClassName;
use PHPStan\Type\VerbosityLevel;
use Rector\ArgTyper\PHPStan\TypeMapper;

/**
 * @implements Collector<MethodCall, array<array{0: string, 1: string, 2: string, 3: string}>>
 */
final class MethodCallArgTypeCollector extends AbstractCallLikeTypeCollector implements Collector
{
    public function getNodeType(): string
    {
        return MethodCall::class;
    }

    /**
     * @param MethodCall $node
     */
    public function processNode(Node $node, Scope $scope): ?array
    {
        if (! $node->name instanceof Identifier) {
            return null;
        }

        if ($node->isFirstClassCallable()) {
            return null;
        }

        // we need at least some args
        if ($node->getArgs() === []) {
            return null;
        }

        $methodCallName = $node->name->toString();
        $callerType = $scope->getType($node->var);

        // @todo check if this can be less strict
        if (! $callerType->isObject()->yes()) {
            return null;
        }

        $this->ensureProjectAutoloadFileIsLoaded($callerType);

        $classNameTypes = [];

        $objectClassReflections = $callerType->getObjectClassReflections();
        foreach ($objectClassReflections as $objectClassReflection) {
            if (! $objectClassReflection->hasMethod($methodCallName)) {
                continue;
            }

            if ($objectClassReflection->isInternal()) {
                continue;
            }

            if ($this->isVendorClass($objectClassReflection)) {
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
}
