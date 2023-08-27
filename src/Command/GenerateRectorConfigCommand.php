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
use TomasVotruba\SherlockTypes\Enum\ConfigFilePath;
use TomasVotruba\SherlockTypes\TemplatePrinter;
use Webmozart\Assert\Assert;

final class GenerateRectorConfigCommand extends Command
{
    public function __construct(
        private readonly SymfonyStyle $symfonyStyle,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // 1. file must exist before runningthis ocmmand
        $recipeFilePath = ConfigFilePath::phpstanCollectedData();

        $phpstanResultJson = $this->loadFileJson($recipeFilePath);
        $configurationContents = $this->createRectorConfigFileContents($phpstanResultJson);

        $rectorGeneratedContents = TemplatePrinter::print(
            __DIR__ . '/../../resources/views/rector-config-template.php',
            [
                '__CONFIGURATION__' => $configurationContents,
            ]
        );

        FileSystem::write(getcwd() . '/rector-generated.php', $rectorGeneratedContents);

        $this->symfonyStyle->success('The "rector-generated.php" file was generated. Now let Rector do its magic!');

        return self::SUCCESS;
    }

    // @todo move somehwere :)

    /**
     * @param array<string, mixed> $phpstanResult
     */
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

    /**
     * @return array<string, mixed>
     */
    private function loadFileJson(string $recipeFilePath): array
    {
        Assert::fileExists($recipeFilePath);
        $fileContents = FileSystem::read($recipeFilePath);

        return Json::decode($fileContents, Json::FORCE_ARRAY);
    }
}
