<?php

declare(strict_types=1);

namespace Rector\ArgTyper\PHPStan\Rule;

use Nette\Utils\FileSystem;
use Nette\Utils\Json;
use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\CollectedDataNode;
use PHPStan\Rules\Rule;
use Rector\ArgTyper\PHPStan\Collectors\PHPUnitAssertMethodCallCollector;

/**
 * @implements Rule<CollectedDataNode>
 */
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

        foreach ($collectedItemsByFile as $collectedItems) {
            foreach ($collectedItems as $collectedItem) {
                $uniqueHash = $collectedItem[1] . $collectedItem[2] . $collectedItem[0];

                $data[$uniqueHash] = [
                    'class' => $collectedItem[1],
                    'method' => $collectedItem[2],
                    'type' => $collectedItem[0],
                ];
            }
        }

        // reset keys
        $data = array_values($data);

        $json = Json::encode($data, Json::PRETTY);
        FileSystem::write(getcwd() . '/rector-recipe.json', $json);

        return [
            'Some error',
        ];
    }
}
