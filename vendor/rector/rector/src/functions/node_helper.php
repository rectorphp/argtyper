<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\RectorPrefix202511\Illuminate\Container\Container;
use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\PrettyPrinter\Standard;
use Argtyper202511\Rector\Console\Style\SymfonyStyleFactory;
use Argtyper202511\Rector\Util\NodePrinter;
use Argtyper202511\RectorPrefix202511\Symfony\Component\Console\Output\OutputInterface;
if (!\function_exists('Argtyper202511\\print_node')) {
    /**
     * @param Node|Node[] $node
     */
    function print_node($node) : void
    {
        $standard = new Standard();
        $nodes = \is_array($node) ? $node : [$node];
        foreach ($nodes as $node) {
            $printedContent = $standard->prettyPrint([$node]);
            \var_dump($printedContent);
        }
    }
}
if (!\function_exists('Argtyper202511\\dump_node')) {
    /**
     * @param Node|Node[] $node
     */
    function dump_node($node) : void
    {
        $rectorStyle = Container::getInstance()->make(SymfonyStyleFactory::class)->create();
        // we turn up the verbosity so it's visible in tests overriding the
        // default which is to be quite during tests
        $rectorStyle->setVerbosity(OutputInterface::VERBOSITY_VERBOSE);
        $rectorStyle->newLine();
        $nodePrinter = new NodePrinter($rectorStyle);
        $nodePrinter->printNodes($node);
    }
}
