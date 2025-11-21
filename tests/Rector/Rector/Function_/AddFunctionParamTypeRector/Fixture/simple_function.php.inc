<?php

namespace Rector\ArgTyper\Tests\Rector\Rector\Function_\AddFunctionParamTypeRector\Fixture;

function simpleFunction($item)
{
}

?>
-----
<?php

namespace Rector\ArgTyper\Tests\Rector\Rector\Function_\AddFunctionParamTypeRector\Fixture;

function simpleFunction(string $item)
{
}

?>
