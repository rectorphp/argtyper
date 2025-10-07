<?php

namespace Rector\ArgTyper\Tests\Rector\Rector\AddParamIterableDocblockTypeRector\Fixture;

final class SomeClass
{
    public function run(array $items)
    {
    }
}

?>
-----
<?php

namespace Rector\ArgTyper\Tests\Rector\Rector\AddParamIterableDocblockTypeRector\Fixture;

final class SomeClass
{
    /**
     * @param string[] $items
     */
    public function run(array $items)
    {
    }
}

?>
