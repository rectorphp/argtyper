<?php

declare(strict_types=1);

namespace Rector\ArgTyper\PHPStan\Rule;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\CollectedDataNode;
use PHPStan\Rules\Rule;
use PHPStan\ShouldNotHappenException;
use Rector\ArgTyper\Enum\ConfigFilePath;
use Rector\ArgTyper\Helpers\FilesLoader;
use Rector\ArgTyper\PHPStan\Collectors\CallLikeArgTypeCollector;
use Rector\ArgTyper\PHPStan\Collectors\FuncCallTypeCollector;

/**
 * @implements Rule<CollectedDataNode>
 *
 * @see \Rector\ArgTyper\PHPStan\Collectors\FuncCallTypeCollector
 */
final class DumpFuncCallArgTypesRule implements Rule
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
        $funcCallCollectedItemsByFile = $node->get(FuncCallTypeCollector::class);

        // nothing to process
        if ($funcCallCollectedItemsByFile === []) {
            return [];
        }

        $data = [];

        foreach ($funcCallCollectedItemsByFile as $funcCallCollectedItems) {
            foreach ($funcCallCollectedItems as $funcCallCollectedItem) {
                foreach ($funcCallCollectedItem as $collectedMethodCallArgType) {

                    $uniqueHash = $collectedMethodCallArgType[0] . $collectedMethodCallArgType[1] . $collectedMethodCallArgType[2];

                    $data[$uniqueHash] = [
                        'function' => $collectedMethodCallArgType[0],
                        'position' => $collectedMethodCallArgType[1],
                        'type' => $collectedMethodCallArgType[2],
                    ];
                }
            }
        }

        // reset keys, to avoid writing irelevant data to json file
        $data = array_values($data);

        if ($data === []) {
            throw new ShouldNotHappenException(
                'We collected some data about types, but rule could not store properly to the json',
            );
        }

        FilesLoader::dumpJsonToFile(ConfigFilePath::funcCalls(), $data);

        // comply with contract, but never used
        return [];
    }
}
