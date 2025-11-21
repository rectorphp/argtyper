<?php

declare (strict_types=1);
namespace RectorPrefix202511\Composer\Pcre\PHPStan;

use RectorPrefix202511\Composer\Pcre\Preg;
use RectorPrefix202511\Composer\Pcre\Regex;
use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr\StaticCall;
use Argtyper202511\PhpParser\Node\Name\FullyQualified;
use Argtyper202511\PHPStan\Analyser\Scope;
use Argtyper202511\PHPStan\Analyser\SpecifiedTypes;
use Argtyper202511\PHPStan\Rules\Rule;
use Argtyper202511\PHPStan\Rules\RuleErrorBuilder;
use Argtyper202511\PHPStan\TrinaryLogic;
use Argtyper202511\PHPStan\Type\ObjectType;
use Argtyper202511\PHPStan\Type\Type;
use Argtyper202511\PHPStan\Type\TypeCombinator;
use Argtyper202511\PHPStan\Type\Php\RegexArrayShapeMatcher;
use function sprintf;
/**
 * @implements Rule<StaticCall>
 */
final class UnsafeStrictGroupsCallRule implements Rule
{
    /**
     * @var RegexArrayShapeMatcher
     */
    private $regexShapeMatcher;
    public function __construct(RegexArrayShapeMatcher $regexShapeMatcher)
    {
        $this->regexShapeMatcher = $regexShapeMatcher;
    }
    public function getNodeType(): string
    {
        return StaticCall::class;
    }
    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node->class instanceof FullyQualified) {
            return [];
        }
        $isRegex = $node->class->toString() === Regex::class;
        $isPreg = $node->class->toString() === Preg::class;
        if (!$isRegex && !$isPreg) {
            return [];
        }
        if (!$node->name instanceof Node\Identifier || !in_array($node->name->name, ['matchStrictGroups', 'isMatchStrictGroups', 'matchAllStrictGroups', 'isMatchAllStrictGroups'], \true)) {
            return [];
        }
        $args = $node->getArgs();
        if (!isset($args[0])) {
            return [];
        }
        $patternArg = $args[0] ?? null;
        if ($isPreg) {
            if (!isset($args[2])) {
                // no matches set, skip as the matches won't be used anyway
                return [];
            }
            $flagsArg = $args[3] ?? null;
        } else {
            $flagsArg = $args[2] ?? null;
        }
        if ($patternArg === null) {
            return [];
        }
        $flagsType = \RectorPrefix202511\Composer\Pcre\PHPStan\PregMatchFlags::getType($flagsArg, $scope);
        if ($flagsType === null) {
            return [];
        }
        $matchedType = $this->regexShapeMatcher->matchExpr($patternArg->value, $flagsType, TrinaryLogic::createYes(), $scope);
        if ($matchedType === null) {
            return [RuleErrorBuilder::message(sprintf('The %s call is potentially unsafe as $matches\' type could not be inferred.', $node->name->name))->identifier('composerPcre.maybeUnsafeStrictGroups')->build()];
        }
        if (count($matchedType->getConstantArrays()) === 1) {
            $matchedType = $matchedType->getConstantArrays()[0];
            $nullableGroups = [];
            foreach ($matchedType->getValueTypes() as $index => $type) {
                if (TypeCombinator::containsNull($type)) {
                    $nullableGroups[] = $matchedType->getKeyTypes()[$index]->getValue();
                }
            }
            if (\count($nullableGroups) > 0) {
                return [RuleErrorBuilder::message(sprintf('The %s call is unsafe as match group%s "%s" %s optional and may be null.', $node->name->name, \count($nullableGroups) > 1 ? 's' : '', implode('", "', $nullableGroups), \count($nullableGroups) > 1 ? 'are' : 'is'))->identifier('composerPcre.unsafeStrictGroups')->build()];
            }
        }
        return [];
    }
}
