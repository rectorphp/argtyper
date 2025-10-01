<?php

declare(strict_types=1);

namespace Rector\ArgTyper\PHPStan\Rule;

use Nette\Utils\Arrays;
use Nette\Utils\FileSystem;
use Nette\Utils\Json;
use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\CollectedDataNode;
use PHPStan\Rules\Rule;
use Rector\ArgTyper\PHPStan\Collectors\MethodCallArgTypeCollector;

/**
 * @implements Rule<CollectedDataNode>
 */
final class DumpMethodCallArgTypesRule implements Rule
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
        $collectedItemsByFile = $node->get(MethodCallArgTypeCollector::class);

        $data = [];

        foreach ($collectedItemsByFile as $collectedItems) {
            foreach ($collectedItems as $collectedItem) {
                foreach ($collectedItem as $collectedMethodCallArgType) {
                    $uniqueHash = $collectedMethodCallArgType[0] . $collectedMethodCallArgType[1] . $collectedMethodCallArgType[2];

                    $data[$uniqueHash] = [
                        'class' => $collectedMethodCallArgType[0],
                        'method' => $collectedMethodCallArgType[1],
                        'position' => $collectedMethodCallArgType[2],
                        'type' => $collectedMethodCallArgType[3],
                    ];
                }
            }
        }

        // reset keys
        $data = array_values($data);

        $json = Json::encode($data, Json::PRETTY);
        FileSystem::write(getcwd() . '/rector-recipe.json', $json);

        // comply with contract, but never used
        return [];
    }
}
