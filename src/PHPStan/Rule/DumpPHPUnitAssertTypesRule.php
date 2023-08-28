<?php

declare (strict_types=1);
namespace TomasVotruba\SherlockTypes\PHPStan\Rule;

use SherlockTypes202308\Nette\Utils\FileSystem;
use SherlockTypes202308\Nette\Utils\Json;
use SherlockTypes202308\PhpParser\Node;
use SherlockTypes202308\PHPStan\Analyser\Scope;
use SherlockTypes202308\PHPStan\Node\CollectedDataNode;
use SherlockTypes202308\PHPStan\Rules\Rule;
use TomasVotruba\SherlockTypes\PHPStan\Collectors\PHPUnitAssertMethodCallCollector;
/**
 * @implements Rule<CollectedDataNode>
 */
final class DumpPHPUnitAssertTypesRule implements Rule
{
    /**
     * @return class-string<Node>
     */
    public function getNodeType() : string
    {
        return CollectedDataNode::class;
    }
    /**
     * @param CollectedDataNode $node
     */
    public function processNode(Node $node, Scope $scope) : array
    {
        $collectedItemsByFile = $node->get(PHPUnitAssertMethodCallCollector::class);
        $data = [];
        foreach ($collectedItemsByFile as $collectedItems) {
            foreach ($collectedItems as $collectedItem) {
                $uniqueHash = $collectedItem[1] . $collectedItem[2] . $collectedItem[0];
                $data[$uniqueHash] = ['class' => $collectedItem[1], 'method' => $collectedItem[2], 'type' => $collectedItem[0]];
            }
        }
        // reset keys
        $data = \array_values($data);
        $json = Json::encode($data, Json::PRETTY);
        FileSystem::write(\getcwd() . '/rector-recipe.json', $json);
        return [];
    }
}
