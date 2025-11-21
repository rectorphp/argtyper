<?php

declare(strict_types=1);

namespace Rector\ArgTyper\Configuration;

use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Function_;
use PHPStan\Type\NullType;
use Rector\ArgTyper\Enum\ConfigFilePath;
use Rector\ArgTyper\Helpers\FilesLoader;
use Rector\ArgTyper\Rector\ValueObject\FuncCallType;
use Webmozart\Assert\Assert;

final class FuncCallTypesConfigurationProvider
{
    /**
     * @var array<FuncCallType>
     */
    private array $funcCallTypes = [];

    /**
     * @return array<int, FuncCallType[]>
     */
    public function matchByPosition(Function_ $function): array
    {
        if (! $function->namespacedName instanceof Name) {
            return [];
        }

        $functionName = $function->namespacedName->toString();

        $functionTypes = $this->provide();

        $matchingFunctionTypes = array_filter(
            $functionTypes,
            fn (FuncCallType $funcCallType): bool => $funcCallType->getFunction() === $functionName
        );

        Assert::allIsInstanceOf($matchingFunctionTypes, FuncCallType::class);

        $typesByPosition = [];

        foreach ($matchingFunctionTypes as $matchingFunctionType) {
            $typesByPosition[$matchingFunctionType->getPosition()][] = $matchingFunctionType;
        }

        return $typesByPosition;
    }

    /**
     * @param array<FuncCallType> $funcCallTypes
     * @api used only in tests
     */
    public function seedTypes(array $funcCallTypes): void
    {
        Assert::allIsInstanceOf($funcCallTypes, FuncCallType::class);
        $this->funcCallTypes = $funcCallTypes;
    }

    /**
     * @return array<FuncCallType>
     */
    private function provide(): array
    {
        if ($this->funcCallTypes !== []) {
            return $this->funcCallTypes;
        }

        $phpstanResultsData = FilesLoader::loadJsonl(ConfigFilePath::funcCalls());

        $dataGroupedByPositionFunctionName = [];

        foreach ($phpstanResultsData as $phpstanResultData) {
            $dataGroupedByPositionFunctionName[$phpstanResultData['function']][$phpstanResultData['position']][] = $phpstanResultData['type'];
        }

        $funcCallTypes = [];

        foreach ($dataGroupedByPositionFunctionName as $functionName => $typesByPosition) {
            foreach ($typesByPosition as $position => $types) {
                $uniqueTypes = array_unique($types);

                if (count($uniqueTypes) === 1) {
                    // easy path, pick sole type
                    $funcCallTypes[] = new FuncCallType($functionName, $position, $uniqueTypes[0]);
                    continue;
                }

                if (in_array(NullType::class, $uniqueTypes) && count($uniqueTypes) === 2) {
                    $typesWithoutNull = array_diff($uniqueTypes, [NullType::class]);

                    $typeWithoutNull = $typesWithoutNull[0];

                    $funcCallTypes[] = new FuncCallType($functionName, $position, $typeWithoutNull, true);
                    continue;
                }

                // log invalid type to improve
                FilesLoader::writeJsonl(getcwd() . '/debug.json', [
                    'skipped_types' => $uniqueTypes,
                ]);
            }
        }

        $this->funcCallTypes = $funcCallTypes;

        return $funcCallTypes;
    }
}
