<?php

declare(strict_types=1);

namespace TomasVotruba\SherlockTypes\PHPStan\Rule;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\CollectedDataNode;
use PHPStan\Rules\Rule;
use TomasVotruba\SherlockTypes\PHPStan\Collectors\PHPUnitAssertMethodCallCollector;

final class DumpPHPUnitAssertTypesRule implements Rule
{
    /**
     * @return class-string<Node>
     */
    public function getNodeType(): string
    {
        return CollectedDataNode::class;
    }

    /**
     * @param CollectedDataNode $node
     */
    public function processNode(Node $node, Scope $scope): array
    {
        $collectedItemsByFile = $node->get(PHPUnitAssertMethodCallCollector::class);

        $data = [];

        foreach ($collectedItemsByFile as $items) {
            foreach ($items as $item) {
                $uniqueHash = $item[1] . $item[2] . $item[0];

                $data[$uniqueHash] = [
                    'class' => $item[1],
                    'method' => $item[2],
                    'type' => $item[0],
                ];
            }
        }

        // reset keys
        $data = array_values($data);

        $json = Json::encode($data, Json::PRETTY);
        FileSystem::write(getcwd() . '/rector-recipe.json', $json);

        return [];
    }
}
