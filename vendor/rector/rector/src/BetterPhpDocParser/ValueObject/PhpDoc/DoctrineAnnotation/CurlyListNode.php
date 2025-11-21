<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\BetterPhpDocParser\ValueObject\PhpDoc\DoctrineAnnotation;

use Argtyper202511\Rector\BetterPhpDocParser\PhpDoc\ArrayItemNode;
use Stringable;
use Argtyper202511\RectorPrefix202511\Webmozart\Assert\Assert;
final class CurlyListNode extends \Argtyper202511\Rector\BetterPhpDocParser\ValueObject\PhpDoc\DoctrineAnnotation\AbstractValuesAwareNode
{
    /**
     * @var ArrayItemNode[]
     * @readonly
     */
    private $arrayItemNodes = [];
    /**
     * @param ArrayItemNode[] $arrayItemNodes
     */
    public function __construct(array $arrayItemNodes = [])
    {
        $this->arrayItemNodes = $arrayItemNodes;
        Assert::allIsInstanceOf($this->arrayItemNodes, ArrayItemNode::class);
        parent::__construct($this->arrayItemNodes);
    }
    public function __toString() : string
    {
        // possibly list items
        return $this->implode($this->values);
    }
    /**
     * @param ArrayItemNode[] $array
     */
    private function implode(array $array) : string
    {
        $itemContents = '';
        \end($array);
        $lastItemKey = \key($array);
        \reset($array);
        foreach ($array as $key => $value) {
            if (\is_int($key)) {
                $itemContents .= (string) $value;
            } else {
                $itemContents .= $key . '=' . $value;
            }
            if ($lastItemKey !== $key) {
                $itemContents .= ', ';
            }
        }
        return '{' . $itemContents . '}';
    }
}
