<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Doctrine\CodeQuality\Rector\Class_;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Stmt\Class_;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Rector\ValueObject\PhpVersionFeature;
use Argtyper202511\Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Doctrine\Tests\CodeQuality\Rector\Class_\RemoveEmptyTableAttributeRector\RemoveEmptyTableAttributeRectorTest
 */
final class RemoveEmptyTableAttributeRector extends AbstractRector implements MinPhpVersionInterface
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition("Remove empty Table attribute on entities because it's useless", [new CodeSample(<<<'CODE_SAMPLE'
<?php

namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\RectorPrefix202511\Doctrine\ORM\Mapping as ORM;
#[ORM\Table]
#[ORM\Entity]
class Product
{
}
\class_alias('Argtyper202511\\Product', 'Argtyper202511\\Product', \false);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
<?php

namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\RectorPrefix202511\Doctrine\ORM\Mapping as ORM;
#[ORM\Entity]
class Product
{
}
\class_alias('Argtyper202511\\Product', 'Argtyper202511\\Product', \false);
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        $hasChanged = \false;
        foreach ($node->attrGroups as $attrGroupKey => $attrGroup) {
            foreach ($attrGroup->attrs as $key => $attribute) {
                if (!$this->isName($attribute, 'Argtyper202511\\Doctrine\\ORM\\Mapping\\Table')) {
                    continue;
                }
                if ($attribute->args !== []) {
                    continue;
                }
                unset($attrGroup->attrs[$key]);
                $hasChanged = \true;
            }
            if ($attrGroup->attrs === []) {
                unset($node->attrGroups[$attrGroupKey]);
            }
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::ATTRIBUTES;
    }
}
