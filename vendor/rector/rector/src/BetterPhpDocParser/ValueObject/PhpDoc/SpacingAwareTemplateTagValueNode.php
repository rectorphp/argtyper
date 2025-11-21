<?php

declare (strict_types=1);
namespace Rector\BetterPhpDocParser\ValueObject\PhpDoc;

use Argtyper202511\PHPStan\PhpDocParser\Ast\PhpDoc\TemplateTagValueNode;
use Argtyper202511\PHPStan\PhpDocParser\Ast\Type\TypeNode;
use Stringable;
final class SpacingAwareTemplateTagValueNode extends TemplateTagValueNode
{
    /**
     * @readonly
     * @var string
     */
    private $preposition;
    public function __construct(string $name, ?TypeNode $typeNode, string $description, string $preposition)
    {
        $this->preposition = $preposition;
        parent::__construct($name, $typeNode, $description);
    }
    public function __toString(): string
    {
        // @see https://github.com/rectorphp/rector/issues/3438
        # 'as'/'of'
        $bound = $this->bound instanceof TypeNode ? ' ' . $this->preposition . ' ' . $this->bound : '';
        $content = $this->name . $bound . ' ' . $this->description;
        return trim($content);
    }
}
