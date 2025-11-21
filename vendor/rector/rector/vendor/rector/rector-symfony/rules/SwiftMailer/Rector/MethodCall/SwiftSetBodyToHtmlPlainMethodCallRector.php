<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Symfony\SwiftMailer\Rector\MethodCall;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Arg;
use Argtyper202511\PhpParser\Node\Expr\MethodCall;
use Argtyper202511\PhpParser\Node\Identifier;
use Argtyper202511\PHPStan\Type\ObjectType;
use Argtyper202511\Rector\PhpParser\Node\Value\ValueResolver;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Symfony\Tests\SwiftMailer\Rector\MethodCall\SwiftSetBodyToHtmlPlainMethodCallRector\SwiftSetBodyToHtmlPlainMethodCallRectorTest
 *
 * @changelog https://github.com/laravel/framework/pull/38481/files#diff-2310168aa86b70a22595ba784039cbdde829bd38245c9586eedd111dfd0f806d
 */
final class SwiftSetBodyToHtmlPlainMethodCallRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\Value\ValueResolver
     */
    private $valueResolver;
    public function __construct(ValueResolver $valueResolver)
    {
        $this->valueResolver = $valueResolver;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Changes setBody() method call on Swift_Message into a html() or plain() based on second argument', [new CodeSample(<<<'CODE_SAMPLE'
$message = new Swift_Message();

$message->setBody('...', 'text/html');

$message->setBody('...', 'text/plain');
$message->setBody('...');
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$message = new Swift_Message();

$message->html('...');

$message->text('...');
$message->text('...');
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [MethodCall::class];
    }
    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->isName($node->name, 'setBody')) {
            return null;
        }
        if (!$this->isObjectType($node->var, new ObjectType('Swift_Message'))) {
            return null;
        }
        if (\count($node->args) === 2) {
            $firstArg = $node->args[1];
            if (!$firstArg instanceof Arg) {
                return null;
            }
            $secondArgValue = $this->valueResolver->getValue($firstArg->value);
            if ($secondArgValue === 'text/html') {
                unset($node->args[1]);
                $node->name = new Identifier('html');
                return $node;
            }
        }
        $node->name = new Identifier('plain');
        return $node;
    }
}
