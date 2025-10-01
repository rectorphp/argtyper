<?php

declare(strict_types=1);

namespace Rector\ArgTyper\Rector;

use Nette\Utils\FileSystem;
use PHPStan\Type\ArrayType;
use PHPStan\Type\Constant\ConstantArrayType;
use Rector\ArgTyper\Rector\ValueObject\ClassMethodType;

/**
 * @see \Rector\ArgTyper\Tests\Rector\RectorConfigPrinterTest
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
        $configurationContents = '';
        foreach ($classMethodTypes as $classMethodType) {
            $printedType = $this->printedType($classMethodType);

            $configurationContents .= sprintf(
                "        new AddReturnTypeDeclaration(%s, '%s', %s)," . PHP_EOL,
                $classMethodType->getClass() . '::class',
                $classMethodType->getMethod(),
                $printedType
            );
        }

        return rtrim($configurationContents);
    }

    private function printedType(ClassMethodType $classMethodType): string
    {
        if (str_starts_with($classMethodType->getType(), 'object:')) {
            return 'new \PHPStan\Type\ObjectType(' . substr($classMethodType->getType(), 7) . '::class)';
        }

        if (in_array($classMethodType->getType(), [ArrayType::class, ConstantArrayType::class], true)) {
            return 'new \PHPStan\Type\ArrayType(new \PHPStan\Type\MixedType(), new PHPStan\Type\MixedType())';
        }

        return 'new ' . $classMethodType->getType() . '()';
    }
}