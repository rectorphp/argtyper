<?php

declare(strict_types=1);

namespace Rector\ArgTyper\Configuration;

use Rector\ArgTyper\Enum\ConfigFilePath;
use Rector\ArgTyper\Helpers\FilesLoader;
use Rector\ArgTyper\Rector\ValueObject\ClassMethodType;

final class ClassMethodTypesConfigurationProvider
{
    /**
     * @var array<ClassMethodType>
     */
    private array $classMethodTypes = [];

    /**
     * @return array<ClassMethodType>
     */
    public function provide(): array
    {
        if ($this->classMethodTypes !== []) {
            return $this->classMethodTypes;
        }

        $phpstanResultsData = FilesLoader::loadFileJson(ConfigFilePath::phpstanCollectedData());

        $dataGroupedByPositionMethodAndClassNames = [];

        foreach ($phpstanResultsData as $singleItemData) {
            $dataGroupedByPositionMethodAndClassNames[$singleItemData['class']][$singleItemData['method']][$singleItemData['position']][] = $singleItemData['type'];
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
                    }  
                        // @todo add support if all the same
                        // use unique types method

                }

            }
        }

        $this->classMethodTypes = $classMethodTypes;

        return $classMethodTypes;
    }
}