<?php

declare(strict_types=1);

namespace Rector\ArgTyper\Configuration;

use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Function_;
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
