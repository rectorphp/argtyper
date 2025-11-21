<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Symfony\Symfony33\Rector\ClassConstFetch;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr\ClassConstFetch;
use Argtyper202511\PhpParser\Node\Scalar\String_;
use Argtyper202511\PHPStan\Type\ObjectType;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * Covers:
 * - https://github.com/symfony/symfony/pull/22441/files
 * - https://github.com/symfony/symfony/blob/master/UPGRADE-3.3.md#console
 *
 * @see \Rector\Symfony\Tests\Symfony33\Rector\ClassConstFetch\ConsoleExceptionToErrorEventConstantRector\ConsoleExceptionToErrorEventConstantRectorTest
 */
final class ConsoleExceptionToErrorEventConstantRector extends AbstractRector
{
    /**
     * @readonly
     * @var \PHPStan\Type\ObjectType
     */
    private $consoleEventsObjectType;
    public function __construct()
    {
        $this->consoleEventsObjectType = new ObjectType('Argtyper202511\\Symfony\\Component\\Console\\ConsoleEvents');
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Turns old event name with EXCEPTION to ERROR constant in Console in Symfony', [new CodeSample('"console.exception"', 'Argtyper202511\\Symfony\\Component\\Console\\ConsoleEvents::ERROR'), new CodeSample('Argtyper202511\\Symfony\\Component\\Console\\ConsoleEvents::EXCEPTION', 'Argtyper202511\\Symfony\\Component\\Console\\ConsoleEvents::ERROR')]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [ClassConstFetch::class, String_::class];
    }
    /**
     * @param ClassConstFetch|String_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($node instanceof ClassConstFetch && ($this->isObjectType($node->class, $this->consoleEventsObjectType) && $this->isName($node->name, 'EXCEPTION'))) {
            return $this->nodeFactory->createClassConstFetch($this->consoleEventsObjectType->getClassName(), 'ERROR');
        }
        if (!$node instanceof String_) {
            return null;
        }
        if ($node->value !== 'console.exception') {
            return null;
        }
        return $this->nodeFactory->createClassConstFetch($this->consoleEventsObjectType->getClassName(), 'ERROR');
    }
}
