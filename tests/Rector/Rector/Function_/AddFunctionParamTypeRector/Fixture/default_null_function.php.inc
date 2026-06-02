<?php

namespace Rector\ArgTyper\Tests\Rector\Rector\Function_\AddFunctionParamTypeRector\Fixture;

function defaultNullFunction($item = null)
{
}

?>
-----
<?php

namespace Rector\ArgTyper\Tests\Rector\Rector\Function_\AddFunctionParamTypeRector\Fixture;

function defaultNullFunction(?string $item = null)
{
}

?>
