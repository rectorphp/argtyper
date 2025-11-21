<?php

declare (strict_types=1);
namespace Argtyper202511\PHPStan\PhpDocParser\Ast\PhpDoc;

use Argtyper202511\PHPStan\PhpDocParser\Ast\NodeAttributes;
class PhpDocTextNode implements \Argtyper202511\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocChildNode
{
    use NodeAttributes;
    /**
     * @var string
     */
    public $text;
    public function __construct(string $text)
    {
        $this->text = $text;
    }
    public function __toString() : string
    {
        return $this->text;
    }
}
