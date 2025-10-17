<?php

declare(strict_types=1);

namespace Rector\ArgTyper\Configuration;

use PhpParser\Node\Stmt\Function_;
use PHPStan\Reflection\ClassReflection;
use Rector\ArgTyper\Enum\ConfigFilePath;
use Rector\ArgTyper\Helpers\FilesLoader;
use Rector\ArgTyper\Rector\ValueObject\ClassMethodType;
use Rector\ArgTyper\Rector\ValueObject\FuncCallType;
use Rector\PHPStan\ScopeFetcher;
use Webmozart\Assert\Assert;

final class FuncCallTypesConfigurationProvider
{
    /**
     * @var array<FuncCallType>
     */
    private array $funcCallTypes = [];

    /**
     * @return FuncCallType[]
     */
    public function matchByPosition(Function_ $function): array
    {
        $scope = ScopeFetcher::fetch($function);

        $classReflection = $scope->getClassReflection();

        if (! $classReflection instanceof ClassReflection) {
            return [];
        }

        $functionTypes = $this->provide();

        $className = $classReflection->getName();
        $methodName = $function->name->toString();

        $matchingFunctionTypes = array_filter($functionTypes, function (FuncCallType $funcCallType) use (
            $className,
            $methodName
        ): bool {
            if ($funcCallType->getFunction() !== $className) {
                return false;
            }

            return $funcCallType->getClass() === $methodName;
        });

        Assert::allIsInstanceOf($matchingFunctionTypes, ClassMethodType::class);

        $typesByPosition = [];

        foreach ($matchingFunctionTypes as $matchingFunctionType) {
            $typesByPosition[$matchingFunctionType->getPosition()][] = $matchingFunctionType;
        }

        return $typesByPosition;
    }

    /**
     * @return array<FuncCallType>
     */
    private function provide(): array
    {
        if ($this->funcCallTypes !== []) {
            return $this->funcCallTypes;
        }

        $phpstanResultsData = FilesLoader::loadFileJson(ConfigFilePath::funcCalls());

        $dataGroupedByPositionFunctionName = [];

        foreach ($phpstanResultsData as $phpstanResultData) {
            $dataGroupedByPositionFunctionName[$phpstanResultData['function']][$phpstanResultData['position']][] = $phpstanResultData['type'];
        }

        $funcCallTypes = [];

        foreach ($dataGroupedByPositionFunctionName as $functionName => $typesByPosition) {
            foreach ($typesByPosition as $position => $types) {
                if (count($types) === 1) {
                    // easy path, pick sole type
                    $funcCallTypes[] = new FuncCallType($functionName, $position, $types[0]);
                } else {
                    // @todo add support if all the same
                    // use unique types method
                    dump(sprintf('Add support for multiple types in "%s":', __METHOD__));
                    dump($types);
                    die;
                }
            }
        }

        $this->funcCallTypes = $funcCallTypes;

        return $funcCallTypes;
    }
}
