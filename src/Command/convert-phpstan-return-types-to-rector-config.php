<?php

declare(strict_types=1);

use Nette\Utils\FileSystem;
use Nette\Utils\Json;
use PHPStan\Type\ArrayType;
use PHPStan\Type\Constant\ConstantArrayType;

$phpstanJsonFileContents = FileSystem::read(__DIR__ . '/../rector-recipe.json');

$phpstanResult = Json::decode($phpstanJsonFileContents, Json::FORCE_ARRAY);

$template = <<<'CODE_SAMPLE'
<?php

use Rector\Config\RectorConfig;
use Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration;
use Rector\TypeDeclaration\Rector\ClassMethod\AddReturnTypeDeclarationRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->ruleWithConfiguration(AddReturnTypeDeclarationRector::class, [
__CONFIGURATION__
    ]);
};
CODE_SAMPLE;

$configurationContents = '';

foreach ($phpstanResult as $singleCase) {
    if (str_starts_with($singleCase['type'], 'object:')) {
        $printedType = ' new \PHPStan\Type\ObjectType(' . substr($singleCase['type'], 7) . '::class)';
    } elseif (in_array($singleCase['type'], [ArrayType::class, ConstantArrayType::class], true)) {
        $printedType = ' new \PHPStan\Type\ArrayType(new \PHPStan\Type\MixedType(), new PHPStan\Type\MixedType())';
    } else {
        $printedType = 'new ' . $singleCase['type'];
    }

    $configurationContents .= sprintf(
        '         new AddReturnTypeDeclaration(%s, "%s", %s),' . PHP_EOL,
        $singleCase['class'] . '::class',
        $singleCase['method'],
        $printedType
    );
}

$rectorGeneratedContents = strtr($template, [
    '__CONFIGURATION__' => $configurationContents,
]);
FileSystem::write(getcwd() . '/rector-generated.php', $rectorGeneratedContents);

echo 'The "rector-generated.php" was generated' . PHP_EOL;
