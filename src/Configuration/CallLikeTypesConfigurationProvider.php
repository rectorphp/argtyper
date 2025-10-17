<?php

declare(strict_types=1);

namespace Rector\ArgTyper\Configuration;

use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Reflection\ClassReflection;
use Rector\ArgTyper\Enum\ConfigFilePath;
use Rector\ArgTyper\Helpers\FilesLoader;
use Rector\ArgTyper\Rector\ValueObject\ClassMethodType;
use Rector\PHPStan\ScopeFetcher;
use Webmozart\Assert\Assert;

final class CallLikeTypesConfigurationProvider
{
    /**
     * @var array<ClassMethodType>
     */
    private array $classMethodTypes = [];

    /**
     * @return array<int, ClassMethodType[]>
     */
    public function matchByPosition(ClassMethod $classMethod): array
    {
        $scope = ScopeFetcher::fetch($classMethod);

        $classReflection = $scope->getClassReflection();
        if (! $classReflection instanceof ClassReflection) {
            return [];
        }

        if ($classReflection->isAnonymous()) {
            return [];
        }

        $classMethodTypes = $this->provide();

        $className = $classReflection->getName();
        $methodName = $classMethod->name->toString();

        $matchingClassMethodTypes = $this->matchByClassAndMethodNames($classMethodTypes, $className, $methodName);
        Assert::allIsInstanceOf($matchingClassMethodTypes, ClassMethodType::class);

        $classMethodTypesByPosition = [];
        foreach ($matchingClassMethodTypes as $matchingClassMethodType) {
            $classMethodTypesByPosition[$matchingClassMethodType->getPosition()][] = $matchingClassMethodType;
        }

        return $classMethodTypesByPosition;
    }

    /**
     * @api used only in tests
     * @param ClassMethodType[] $classMethodTypes
     */
    public function seedClassMethodTypes(array $classMethodTypes): void
    {
        Assert::allIsInstanceOf($classMethodTypes, ClassMethodType::class);

        $this->classMethodTypes = $classMethodTypes;
    }

    /**
     * @return array<ClassMethodType>
     */
    private function provide(): array
    {
        if ($this->classMethodTypes !== []) {
            return $this->classMethodTypes;
        }

        $phpstanResultsData = FilesLoader::loadFileJson(ConfigFilePath::callLikes());

        $dataGroupedByPositionMethodAndClassNames = [];

        foreach ($phpstanResultsData as $phpstanResultData) {
            $dataGroupedByPositionMethodAndClassNames[$phpstanResultData['class']][$phpstanResultData['method']][$phpstanResultData['position']][] = $phpstanResultData['type'];
        }

        $classMethodTypes = [];

        foreach ($dataGroupedByPositionMethodAndClassNames as $className => $typesByPositionByMethodNames) {
            foreach ($typesByPositionByMethodNames as $methodName => $typesByPosition) {
                foreach ($typesByPosition as $position => $types) {
                    if (count($types) === 1) {
                        // easy path, pick sole type
                        $classMethodTypes[] = new \Rector\ArgTyper\Rector\ValueObject\ClassMethodType(
                            $className,
                            $methodName,
                            $position,
                            $types[0]
                        );
                    } else {
                        // @todo add support if all the same
                        // use unique types method
                        dump(sprintf('Add support for multiple types in "%s":', __METHOD__));
                        dump($types);
                        die;
                    }
                }
            }
        }

        $this->classMethodTypes = $classMethodTypes;

        return $classMethodTypes;
    }

    /**
     * @param array<ClassMethodType> $classMethodTypes
     * @return array<ClassMethodType>
     */
    private function matchByClassAndMethodNames(array $classMethodTypes, string $className, string $methodName): array
    {
        return array_filter(
            $classMethodTypes,
            function (ClassMethodType $classMethodType) use (
                $className,
                $methodName
            ): bool {
                if ($classMethodType->getClass() !== $className) {
                    return false;
                }

                return $classMethodType->getMethod() === $methodName;
            }
        );
    }
}
