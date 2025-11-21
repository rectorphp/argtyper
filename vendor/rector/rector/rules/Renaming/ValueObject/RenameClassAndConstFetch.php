<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Renaming\ValueObject;

use Argtyper202511\PHPStan\Type\ObjectType;
use Argtyper202511\Rector\Renaming\Contract\RenameClassConstFetchInterface;
use Argtyper202511\Rector\Validation\RectorAssert;
final class RenameClassAndConstFetch implements RenameClassConstFetchInterface
{
    /**
     * @readonly
     * @var string
     */
    private $oldClass;
    /**
     * @readonly
     * @var string
     */
    private $oldConstant;
    /**
     * @readonly
     * @var string
     */
    private $newClass;
    /**
     * @readonly
     * @var string
     */
    private $newConstant;
    public function __construct(string $oldClass, string $oldConstant, string $newClass, string $newConstant)
    {
        $this->oldClass = $oldClass;
        $this->oldConstant = $oldConstant;
        $this->newClass = $newClass;
        $this->newConstant = $newConstant;
        RectorAssert::className($oldClass);
        RectorAssert::constantName($oldConstant);
        RectorAssert::className($newClass);
        RectorAssert::constantName($newConstant);
    }
    public function getOldObjectType() : ObjectType
    {
        return new ObjectType($this->oldClass);
    }
    public function getOldConstant() : string
    {
        return $this->oldConstant;
    }
    public function getNewConstant() : string
    {
        return $this->newConstant;
    }
    public function getNewClass() : string
    {
        return $this->newClass;
    }
}
