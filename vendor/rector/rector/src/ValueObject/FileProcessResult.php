<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\ValueObject;

use Argtyper202511\Rector\ValueObject\Error\SystemError;
use Argtyper202511\Rector\ValueObject\Reporting\FileDiff;
use Argtyper202511\RectorPrefix202511\Webmozart\Assert\Assert;
final class FileProcessResult
{
    /**
     * @var SystemError[]
     * @readonly
     */
    private $systemErrors;
    /**
     * @readonly
     * @var \Rector\ValueObject\Reporting\FileDiff|null
     */
    private $fileDiff;
    /**
     * @readonly
     * @var bool
     */
    private $hasChanged;
    /**
     * @param SystemError[] $systemErrors
     */
    public function __construct(array $systemErrors, ?FileDiff $fileDiff, bool $hasChanged)
    {
        $this->systemErrors = $systemErrors;
        $this->fileDiff = $fileDiff;
        $this->hasChanged = $hasChanged;
        Assert::allIsInstanceOf($systemErrors, SystemError::class);
    }
    /**
     * @return SystemError[]
     */
    public function getSystemErrors() : array
    {
        return $this->systemErrors;
    }
    public function getFileDiff() : ?FileDiff
    {
        return $this->fileDiff;
    }
    public function hasChanged() : bool
    {
        return $this->hasChanged;
    }
}
