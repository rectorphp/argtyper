<?php

declare(strict_types=1);

namespace TomasVotruba\SherlockTypes\Rector;

use Nette\Utils\FileSystem;
use PHPStan\Type\ArrayType;
use PHPStan\Type\Constant\ConstantArrayType;
use TomasVotruba\SherlockTypes\ValueObject\ClassMethodType;

/**
 * @see \TomasVotruba\SherlockTypes\Tests\Rector\RectorConfigPrinterTest
 */
final class RectorConfigPrinter
{
    /**
     * @param ClassMethodType[] $classMethodTypes
     */
    public function print(array $classMethodTypes): string
    {
        $configurationContents = $this->createRectorConfigFileContents($classMethodTypes);
        $templateContents = FileSystem::read(__DIR__ . '/../../resources/views/rector-config-template.php');

        return strtr($templateContents, [
            '__CONFIGURATION__' => $configurationContents,
        ]);
    }

    /**
     * @param ClassMethodType[] $classMethodTypes
     */
    private function createRectorConfigFileContents(array $classMethodTypes): string
    {
        var_dump($classMethodTypes);
        die;

        $configurationContents = '';
        foreach ($phpstanResult as $singleCase) {
            if (str_starts_with((string) $singleCase['type'], 'object:')) {
                $printedType = ' new \PHPStan\Type\ObjectType(' . substr((string) $singleCase['type'], 7) . '::class)';
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

        return $configurationContents;
    }
}