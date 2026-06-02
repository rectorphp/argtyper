<?php

namespace Rector\ArgTyper\Tests\Rector\Rector\ClassMethod\AddClassMethodParamTypeRector\Fixture;

final class AddNullableForDefaultNull
{
    public function run($value = null)
    {
    }
}

?>
-----
<?php

namespace Rector\ArgTyper\Tests\Rector\Rector\ClassMethod\AddClassMethodParamTypeRector\Fixture;

final class AddNullableForDefaultNull
{
    public function run(?string $value = null)
    {
    }
}

?>
