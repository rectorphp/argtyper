<?php

declare (strict_types=1);
namespace Rector\Symfony\NodeFactory;

use Argtyper202511\PhpParser\Node\ArrayItem;
use Argtyper202511\PhpParser\Node\Expr\Array_;
use Argtyper202511\PhpParser\Node\Expr\FuncCall;
use Argtyper202511\PhpParser\Node\Expr\Variable;
use Argtyper202511\PhpParser\Node\Scalar\String_;
use Rector\NodeManipulator\FuncCallManipulator;
final class ArrayFromCompactFactory
{
    /**
     * @readonly
     * @var \Rector\NodeManipulator\FuncCallManipulator
     */
    private $funcCallManipulator;
    public function __construct(FuncCallManipulator $funcCallManipulator)
    {
        $this->funcCallManipulator = $funcCallManipulator;
    }
    public function createArrayFromCompactFuncCall(FuncCall $compactFuncCall): Array_
    {
        $compactVariableNames = $this->funcCallManipulator->extractArgumentsFromCompactFuncCalls([$compactFuncCall]);
        $array = new Array_();
        foreach ($compactVariableNames as $compactVariableName) {
            $arrayItem = new ArrayItem(new Variable($compactVariableName), new String_($compactVariableName));
            $array->items[] = $arrayItem;
        }
        return $array;
    }
}
