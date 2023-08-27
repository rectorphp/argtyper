<?php

declare(strict_types=1);

namespace TomasVotruba\SherlockTypes\Command;

use Nette\Utils\FileSystem;
use Nette\Utils\Json;
use PHPStan\Type\ArrayType;
use PHPStan\Type\Constant\ConstantArrayType;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class GenerateRectorConfigCommand extends Command
{
    public function __construct(
        private readonly SymfonyStyle $symfonyStyle,
    )
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $recipeFilePath = getcwd() . '/rector-recipe.json';
        $phpstanJsonFileContents = FileSystem::read($recipeFilePath);

        $phpstanResult = Json::decode($phpstanJsonFileContents, Json::FORCE_ARRAY);

        $configurationContents = $this->createRectorConfigFileContents($phpstanResult);

        $templateContents = FileSystem::read(__DIR__ . '/../../resources/views/rector-config-template.php');

        $rectorGeneratedContents = strtr($templateContents, [
            '__CONFIGURATION__' => $configurationContents,
        ]);

        FileSystem::write(getcwd() . '/rector-generated.php', $rectorGeneratedContents);

        $this->symfonyStyle->write('The "rector-generated.php" was generated');

        return self::SUCCESS;
    }

    private function createRectorConfigFileContents(array $phpstanResult): string
    {
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
