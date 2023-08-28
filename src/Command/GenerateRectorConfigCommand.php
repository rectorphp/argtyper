<?php

declare (strict_types=1);
namespace TomasVotruba\SherlockTypes\Command;

use SherlockTypes202308\Nette\Utils\FileSystem;
use SherlockTypes202308\Symfony\Component\Console\Command\Command;
use SherlockTypes202308\Symfony\Component\Console\Input\InputInterface;
use SherlockTypes202308\Symfony\Component\Console\Output\OutputInterface;
use SherlockTypes202308\Symfony\Component\Console\Style\SymfonyStyle;
use TomasVotruba\SherlockTypes\Enum\ConfigFilePath;
use TomasVotruba\SherlockTypes\Helpers\FilesLoader;
use TomasVotruba\SherlockTypes\Rector\RectorConfigPrinter;
final class GenerateRectorConfigCommand extends Command
{
    public function __construct(private readonly SymfonyStyle $symfonyStyle, private readonly RectorConfigPrinter $rectorConfigPrinter)
    {
        parent::__construct();
    }
    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $recipeFilePath = ConfigFilePath::phpstanCollectedData();
        $phpstanResultsData = FilesLoader::loadFileJson($recipeFilePath);
        $rectorConfigContents = $this->rectorConfigPrinter->print($phpstanResultsData);
        FileSystem::write(ConfigFilePath::rectorGeneratedConfig(), $rectorConfigContents);
        $this->symfonyStyle->success('The "rector-generated.php" file was generated. Now let Rector do its magic!');
        return self::SUCCESS;
    }
}
