<?php

declare (strict_types=1);
namespace Rector\Transform\ValueObject;

use Argtyper202511\PhpParser\Node\Expr\ClassConstFetch;
use Argtyper202511\PhpParser\Node\Expr\ConstFetch;
use Argtyper202511\PhpParser\Node\Scalar\Float_;
use Argtyper202511\PhpParser\Node\Scalar\Int_;
use Argtyper202511\PhpParser\Node\Scalar\String_;
final class ScalarValueToConstFetch
{
    /**
     * @readonly
     * @var \PhpParser\Node\Scalar\Float_|\PhpParser\Node\Scalar\String_|\PhpParser\Node\Scalar\Int_
     */
    private $scalar;
    /**
     * @readonly
     * @var \PhpParser\Node\Expr\ConstFetch|\PhpParser\Node\Expr\ClassConstFetch
     */
    private $constFetch;
    /**
     * @param \PhpParser\Node\Scalar\Float_|\PhpParser\Node\Scalar\String_|\PhpParser\Node\Scalar\Int_ $scalar
     * @param \PhpParser\Node\Expr\ConstFetch|\PhpParser\Node\Expr\ClassConstFetch $constFetch
     */
    public function __construct($scalar, $constFetch)
    {
        $this->scalar = $scalar;
        $this->constFetch = $constFetch;
    }
    /**
     * @return \PhpParser\Node\Scalar\Float_|\PhpParser\Node\Scalar\String_|\PhpParser\Node\Scalar\Int_
     */
    public function getScalar()
    {
        return $this->scalar;
    }
    /**
     * @return \PhpParser\Node\Expr\ConstFetch|\PhpParser\Node\Expr\ClassConstFetch
     */
    public function getConstFetch()
    {
        return $this->constFetch;
    }
}
