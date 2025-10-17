<?php

declare(strict_types=1);

namespace Rector\ArgTyper\PHPStan\Rule;

use Nette\Utils\FileSystem;
use Nette\Utils\Json;
use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\CollectedDataNode;
use PHPStan\Rules\Rule;
use PHPStan\ShouldNotHappenException;
use Rector\ArgTyper\Enum\ConfigFilePath;
use Rector\ArgTyper\PHPStan\Collectors\CallLikeArgTypeCollector;
use Rector\ArgTyper\PHPStan\Collectors\StaticCallArgTypeCollector;

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
        $methodCallCollectedItemsByFile = $node->get(CallLikeArgTypeCollector::class);
        $staticCallCollectedItemsByFile = $node->get(StaticCallArgTypeCollector::class);

        $callLikeCollectedItemsByFile = array_merge_recursive(
            $methodCallCollectedItemsByFile ?? [],
            $staticCallCollectedItemsByFile ?? []
        );

        // nothing to process
        if ($callLikeCollectedItemsByFile === []) {
            return [];
        }

        $data = [];

        foreach ($callLikeCollectedItemsByFile as $callLikeCollectedItems) {
            foreach ($callLikeCollectedItems as $callLikeCollectedItem) {
                foreach ($callLikeCollectedItem as $collectedMethodCallArgType) {

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

        if ($data === []) {
            throw new ShouldNotHappenException(
                'We collected some data about types , but rule could not store properly to the json',
            );
        }

        $jsonString = Json::encode($data, Json::PRETTY);

        FileSystem::write(ConfigFilePath::phpstanCollectedData(), $jsonString);

        // comply with contract, but never used
        return [];
    }
}
