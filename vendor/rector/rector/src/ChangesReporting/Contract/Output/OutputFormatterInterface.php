<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\ChangesReporting\Contract\Output;

use Argtyper202511\Rector\ValueObject\Configuration;
use Argtyper202511\Rector\ValueObject\ProcessResult;
interface OutputFormatterInterface
{
    public function getName() : string;
    public function report(ProcessResult $processResult, Configuration $configuration) : void;
}
