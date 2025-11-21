<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\PhpAttribute\AnnotationToAttributeMapper;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr\ClassConstFetch;
use Argtyper202511\PhpParser\Node\Name;
use Argtyper202511\PhpParser\Node\Scalar\String_;
use Argtyper202511\Rector\PhpAttribute\Contract\AnnotationToAttributeMapperInterface;
use Argtyper202511\Rector\Validation\RectorAssert;
use Argtyper202511\RectorPrefix202511\Webmozart\Assert\InvalidArgumentException;
/**
 * @implements AnnotationToAttributeMapperInterface<string>
 */
final class ClassConstFetchAnnotationToAttributeMapper implements AnnotationToAttributeMapperInterface
{
    /**
     * @param mixed $value
     */
    public function isCandidate($value): bool
    {
        if (!is_string($value)) {
            return \false;
        }
        if (strpos($value, '::') === \false) {
            return \false;
        }
        // is quoted? skip it
        return strncmp($value, '"', strlen('"')) !== 0;
    }
    /**
     * @param string $value
     * @return String_|ClassConstFetch
     */
    public function map($value): Node
    {
        $values = explode('::', $value);
        if (count($values) !== 2) {
            return new String_($value);
        }
        [$class, $constant] = $values;
        if ($class === '') {
            return new String_($value);
        }
        try {
            RectorAssert::className(ltrim($class, '\\'));
            RectorAssert::constantName($constant);
        } catch (InvalidArgumentException $exception) {
            return new String_($value);
        }
        return new ClassConstFetch(new Name($class), $constant);
    }
}
