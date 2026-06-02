<?php

namespace Rector\ArgTyper\Tests\Rector\Rector\ClassMethod\AddClassMethodParamTypeRector\Fixture;

final class AddNullableScalarFromNullDefault
{
    public function translate($locale = null)
    {
    }
}

?>
-----
<?php

namespace Rector\ArgTyper\Tests\Rector\Rector\ClassMethod\AddClassMethodParamTypeRector\Fixture;

final class AddNullableScalarFromNullDefault
{
    public function translate(?string $locale = null)
    {
    }
}

?>
