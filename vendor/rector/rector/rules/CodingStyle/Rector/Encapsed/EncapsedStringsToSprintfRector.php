<?php

declare (strict_types=1);
namespace Rector\CodingStyle\Rector\Encapsed;

use RectorPrefix202511\Nette\Utils\Strings;
use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Arg;
use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp\Concat;
use Argtyper202511\PhpParser\Node\Expr\ConstFetch;
use Argtyper202511\PhpParser\Node\Expr\FuncCall;
use Argtyper202511\PhpParser\Node\Expr\Variable;
use Argtyper202511\PhpParser\Node\InterpolatedStringPart;
use Argtyper202511\PhpParser\Node\Name;
use Argtyper202511\PhpParser\Node\Scalar\InterpolatedString;
use Argtyper202511\PhpParser\Node\Scalar\String_;
use Argtyper202511\PHPStan\Type\Type;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Rector\AbstractRector;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodingStyle\Rector\Encapsed\EncapsedStringsToSprintfRector\EncapsedStringsToSprintfRectorTest
 */
final class EncapsedStringsToSprintfRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @api
     * @var string
     */
    public const ALWAYS = 'always';
    /**
     * @var array<string, array<class-string<Type>>>
     */
    private const FORMAT_SPECIFIERS = ['%s' => ['Argtyper202511\PHPStan\Type\StringType'], '%d' => ['Argtyper202511\PHPStan\Type\Constant\ConstantIntegerType', 'Argtyper202511\PHPStan\Type\IntegerRangeType', 'Argtyper202511\PHPStan\Type\IntegerType']];
    /**
     * @var bool
     */
    private $always = \false;
    /**
     * @var string
     */
    private $sprintfFormat = '';
    /**
     * @var Expr[]
     */
    private $argumentVariables = [];
    /**
     * @param array<string, mixed> $configuration
     */
    public function configure(array $configuration): void
    {
        $this->always = $configuration[self::ALWAYS] ?? \false;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Convert enscaped {$string} to more readable sprintf or concat, if no mask is used', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
echo "Unsupported format {$format} - use another";

echo "Try {$allowed}";
CODE_SAMPLE
, <<<'CODE_SAMPLE'
echo sprintf('Unsupported format %s - use another', $format);

echo 'Try ' . $allowed;
CODE_SAMPLE
, [self::ALWAYS => \false]), new ConfiguredCodeSample(<<<'CODE_SAMPLE'
echo "Unsupported format {$format} - use another";

echo "Try {$allowed}";
CODE_SAMPLE
, <<<'CODE_SAMPLE'
echo sprintf('Unsupported format %s - use another', $format);

echo sprintf('Try %s', $allowed);
CODE_SAMPLE
, [self::ALWAYS => \true])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [InterpolatedString::class];
    }
    /**
     * @param InterpolatedString $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }
        $this->sprintfFormat = '';
        $this->argumentVariables = [];
        foreach ($node->parts as $part) {
            if ($part instanceof InterpolatedStringPart) {
                $this->collectEncapsedStringPart($part);
            } else {
                $this->collectExpr($part);
            }
        }
        return $this->createSprintfFuncCallOrConcat($this->sprintfFormat, $this->argumentVariables);
    }
    private function shouldSkip(InterpolatedString $interpolatedString): bool
    {
        if ($interpolatedString->hasAttribute(AttributeKey::DOC_LABEL)) {
            return \true;
        }
        foreach ($interpolatedString->parts as $part) {
            if (!$part instanceof InterpolatedStringPart) {
                continue;
            }
            if ($this->containsControlASCIIChar($part->value)) {
                return \true;
            }
        }
        return \false;
    }
    private function collectEncapsedStringPart(InterpolatedStringPart $interpolatedStringPart): void
    {
        $stringValue = $interpolatedStringPart->value;
        if ($stringValue === "\n") {
            $this->argumentVariables[] = new ConstFetch(new Name('PHP_EOL'));
            $this->sprintfFormat .= '%s';
            return;
        }
        $this->sprintfFormat .= Strings::replace($stringValue, '#%#', '%%');
    }
    private function collectExpr(Expr $expr): void
    {
        $type = $this->nodeTypeResolver->getType($expr);
        $found = \false;
        foreach (self::FORMAT_SPECIFIERS as $key => $types) {
            if (in_array(get_class($type), $types, \true)) {
                $this->sprintfFormat .= $key;
                $found = \true;
                break;
            }
        }
        if (!$found) {
            $this->sprintfFormat .= '%s';
        }
        // remove: ${wrap} → $wrap
        if ($expr instanceof Variable) {
            $expr->setAttribute(AttributeKey::ORIGINAL_NODE, null);
        }
        $this->argumentVariables[] = $expr;
    }
    /**
     * @param Expr[] $argumentVariables
     * @return \PhpParser\Node\Expr\BinaryOp\Concat|\PhpParser\Node\Expr\FuncCall|\PhpParser\Node\Expr|null
     */
    private function createSprintfFuncCallOrConcat(string $mask, array $argumentVariables)
    {
        $bareMask = str_repeat('%s', count($argumentVariables));
        if ($mask === $bareMask) {
            if (count($argumentVariables) === 1) {
                return $argumentVariables[0];
            }
            return $this->nodeFactory->createConcat($argumentVariables);
        }
        if (!$this->always) {
            $singleValueConcat = $this->createSingleValueEdgeConcat($argumentVariables, $mask);
            if ($singleValueConcat instanceof Concat) {
                return $singleValueConcat;
            }
        }
        // checks for windows or linux line ending. \n is contained in both.
        if (strpos($mask, "\n") !== \false) {
            return null;
        }
        $string = $this->createString($mask);
        $arguments = [new Arg($string)];
        foreach ($argumentVariables as $argumentVariable) {
            $arguments[] = new Arg($argumentVariable);
        }
        return new FuncCall(new Name('sprintf'), $arguments);
    }
    /**
     * @param Expr[] $argumentVariables
     */
    private function createSingleValueEdgeConcat(array $argumentVariables, string $mask): ?Concat
    {
        if (count($argumentVariables) !== 1) {
            return null;
        }
        if (substr_count($mask, '%s') !== 1 && substr_count($mask, '%d') !== 1) {
            return null;
        }
        $cleanMask = Strings::replace($mask, '#\%\%#', '%');
        if (substr_compare($mask, '%s', -strlen('%s')) === 0 || substr_compare($mask, '%d', -strlen('%d')) === 0) {
            $bareString = new String_((string) substr($cleanMask, 0, -2));
            return new Concat($bareString, $argumentVariables[0]);
        }
        if (strncmp($mask, '%s', strlen('%s')) === 0 || strncmp($mask, '%d', strlen('%d')) === 0) {
            $bareString = new String_((string) substr($cleanMask, 2));
            return new Concat($argumentVariables[0], $bareString);
        }
        return null;
    }
    private function createString(string $value): String_
    {
        $kind = strpos($value, "'") !== \false ? String_::KIND_DOUBLE_QUOTED : String_::KIND_SINGLE_QUOTED;
        return new String_($value, ['kind' => $kind]);
    }
    private function containsControlASCIIChar(string $content): bool
    {
        return (bool) Strings::match($content, '#[\x00-\x08\x0B\x0C\x0E-\x1F]#');
    }
}
