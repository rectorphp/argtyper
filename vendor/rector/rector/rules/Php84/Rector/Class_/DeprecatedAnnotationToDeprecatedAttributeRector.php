<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Php84\Rector\Class_;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Stmt\ClassConst;
use Argtyper202511\PhpParser\Node\Stmt\ClassMethod;
use Argtyper202511\PhpParser\Node\Stmt\Function_;
use Argtyper202511\Rector\PhpAttribute\DeprecatedAnnotationToDeprecatedAttributeConverter;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Rector\ValueObject\PhpVersionFeature;
use Argtyper202511\Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Php84\Rector\Class_\DeprecatedAnnotationToDeprecatedAttributeRector\DeprecatedAnnotationToDeprecatedAttributeRectorTest
 */
final class DeprecatedAnnotationToDeprecatedAttributeRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \Rector\PhpAttribute\DeprecatedAnnotationToDeprecatedAttributeConverter
     */
    private $deprecatedAnnotationToDeprecatedAttributeConverter;
    public function __construct(DeprecatedAnnotationToDeprecatedAttributeConverter $deprecatedAnnotationToDeprecatedAttributeConverter)
    {
        $this->deprecatedAnnotationToDeprecatedAttributeConverter = $deprecatedAnnotationToDeprecatedAttributeConverter;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change @deprecated annotation to Deprecated attribute', [new CodeSample(<<<'CODE_SAMPLE'
/**
 * @deprecated 1.0.0 Use SomeOtherFunction instead
 */
function someFunction()
{
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
#[\Deprecated(message: 'Use SomeOtherFunction instead', since: '1.0.0')]
function someFunction()
{
}
CODE_SAMPLE
)]);
    }
    public function getNodeTypes() : array
    {
        return [Function_::class, ClassMethod::class, ClassConst::class];
    }
    /**
     * @param ClassConst|Function_|ClassMethod $node
     */
    public function refactor(Node $node) : ?Node
    {
        return $this->deprecatedAnnotationToDeprecatedAttributeConverter->convert($node);
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::DEPRECATED_ATTRIBUTE;
    }
}
