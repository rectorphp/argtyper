<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\TypeDeclaration\Rector\ClassMethod;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Stmt\ClassMethod;
use Argtyper202511\PhpParser\Node\Stmt\Function_;
use Argtyper202511\Rector\PHPStan\ScopeFetcher;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Rector\TypeDeclaration\NodeManipulator\AddReturnTypeFromStrictNativeCall;
use Argtyper202511\Rector\ValueObject\PhpVersion;
use Argtyper202511\Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictNativeCallRector\ReturnTypeFromStrictNativeCallRectorTest
 */
final class ReturnTypeFromStrictNativeCallRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\NodeManipulator\AddReturnTypeFromStrictNativeCall
     */
    private $addReturnTypeFromStrictNativeCall;
    public function __construct(AddReturnTypeFromStrictNativeCall $addReturnTypeFromStrictNativeCall)
    {
        $this->addReturnTypeFromStrictNativeCall = $addReturnTypeFromStrictNativeCall;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add strict return type based native function or native method', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    public function run()
    {
        return strlen('value');
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    public function run(): int
    {
        return strlen('value');
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [ClassMethod::class, Function_::class];
    }
    /**
     * @param ClassMethod|Function_ $node
     */
    public function refactor(Node $node): ?Node
    {
        $scope = ScopeFetcher::fetch($node);
        return $this->addReturnTypeFromStrictNativeCall->add($node, $scope);
    }
    public function provideMinPhpVersion(): int
    {
        return PhpVersion::PHP_70;
    }
}
