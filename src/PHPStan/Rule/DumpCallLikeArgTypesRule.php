<?php

declare(strict_types=1);

namespace Rector\ArgTyper\PHPStan\Rule;

use Nette\Utils\Json;
use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\CollectedDataNode;
use PHPStan\Rules\Rule;
use PHPStan\ShouldNotHappenException;
use Rector\ArgTyper\Enum\ConfigFilePath;
use Rector\ArgTyper\PHPStan\Collectors\CallLikeArgTypeCollector;

/**
 * @implements Rule<CollectedDataNode>
 */
final class DumpCallLikeArgTypesRule implements Rule
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
        $callLikeCollectedItemsByFile = $node->get(CallLikeArgTypeCollector::class);

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

        $jsonString = json_encode($data, Json::PRETTY);
        file_put_contents(ConfigFilePath::phpstanCollectedData(), $jsonString);

        // comply with contract, but never used
        return [];
    }
}
