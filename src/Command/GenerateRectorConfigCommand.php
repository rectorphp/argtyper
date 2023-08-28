<?php

declare(strict_types=1);

namespace TomasVotruba\SherlockTypes\Command;

use Nette\Utils\FileSystem;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TomasVotruba\SherlockTypes\Enum\ConfigFilePath;
use TomasVotruba\SherlockTypes\Helpers\FilesLoader;
use TomasVotruba\SherlockTypes\Rector\RectorConfigPrinter;

final class GenerateRectorConfigCommand extends Command
{
    public function __construct(
        private readonly SymfonyStyle $symfonyStyle,
        private readonly RectorConfigPrinter $rectorConfigPrinter,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('generate-rector-config');
        $this->setDescription('Load PHPStan json report and generate Rector config from it');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $recipeFilePath = ConfigFilePath::phpstanCollectedData();

        $phpstanResultsData = FilesLoader::loadFileJson($recipeFilePath);
        $rectorConfigContents = $this->rectorConfigPrinter->print($phpstanResultsData);

        FileSystem::write(ConfigFilePath::rectorGeneratedConfig(), $rectorConfigContents);

        $this->symfonyStyle->success('The "rector-generated.php" file was generated. Now let Rector do its magic!');

        return self::SUCCESS;
    }
}
