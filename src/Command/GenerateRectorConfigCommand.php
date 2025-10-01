<?php

declare(strict_types=1);

namespace Rector\ArgTyper\Command;

use Nette\Utils\FileSystem;
use Rector\ArgTyper\Enum\ConfigFilePath;
use Rector\ArgTyper\Helpers\FilesLoader;
use Rector\ArgTyper\Rector\RectorConfigPrinter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

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

        $dataGroupedByPositionMethodAndClassNames = [];

        foreach ($phpstanResultsData as $singleItemData) {
            $dataGroupedByPositionMethodAndClassNames[$singleItemData['class']][$singleItemData['method']][$singleItemData['position']][] = $singleItemData['type'];
        }

        $classMethodTypes = [];

        foreach ($dataGroupedByPositionMethodAndClassNames as $className => $typesByPositionByMethodNames) {
            foreach ($typesByPositionByMethodNames as $methodName => $typesByPosition) {
                foreach ($typesByPosition as $position => $types) {
                    if (count($types) === 1) {
                        // easy path, pick sole type

                        $classMethodTypes[] = new \Rector\ArgTyper\Rector\ValueObject\ClassMethodType(
                            $className,
                            $methodName,
                            $position,
                            $types[0]
                        );
                    }  
                        // @todo add support if all the same
                        // use unique types method

                }

            }
        }

        $rectorConfigContents = $this->rectorConfigPrinter->print($classMethodTypes);

        FileSystem::write(ConfigFilePath::rectorGeneratedConfig(), $rectorConfigContents);

        $this->symfonyStyle->success('The "rector-generated.php" file was generated. Now let Rector do its magic!');

        return self::SUCCESS;
    }
}
