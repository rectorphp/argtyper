<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Differ;

use Argtyper202511\RectorPrefix202511\SebastianBergmann\Diff\Differ;
use Argtyper202511\RectorPrefix202511\SebastianBergmann\Diff\Output\StrictUnifiedDiffOutputBuilder;
final class DefaultDiffer
{
    /**
     * @readonly
     * @var \RectorPrefix202511\SebastianBergmann\Diff\Differ
     */
    private $differ;
    public function __construct()
    {
        $strictUnifiedDiffOutputBuilder = new StrictUnifiedDiffOutputBuilder(['fromFile' => 'Original', 'toFile' => 'New']);
        $this->differ = new Differ($strictUnifiedDiffOutputBuilder);
    }
    public function diff(string $old, string $new) : string
    {
        return $this->differ->diff($old, $new);
    }
}
