<?php

declare (strict_types=1);
namespace TomasVotruba\SherlockTypes\DependencyInjection;

use SherlockTypes202308\Illuminate\Container\Container;
use SherlockTypes202308\PhpParser\Parser;
use SherlockTypes202308\PhpParser\ParserFactory;
use SherlockTypes202308\Symfony\Component\Console\Application;
use SherlockTypes202308\Symfony\Component\Console\Input\ArrayInput;
use SherlockTypes202308\Symfony\Component\Console\Output\ConsoleOutput;
use SherlockTypes202308\Symfony\Component\Console\Output\NullOutput;
use SherlockTypes202308\Symfony\Component\Console\Style\SymfonyStyle;
use TomasVotruba\SherlockTypes\Helpers\PrivatesAccessor;
final class ContainerFactory
{
    /**
     * @api
     */
    public function create() : Container
    {
        $container = new Container();
        $container->singleton(Parser::class, static function () : Parser {
            $parserFactory = new ParserFactory();
            return $parserFactory->create(ParserFactory::PREFER_PHP7);
        });
        $container->singleton(SymfonyStyle::class, static function () : SymfonyStyle {
            // use null output ofr tests to avoid printing
            $consoleOutput = \defined('PHPUNIT_COMPOSER_INSTALL') ? new NullOutput() : new ConsoleOutput();
            return new SymfonyStyle(new ArrayInput([]), $consoleOutput);
        });
        $container->singleton(Application::class, function (Container $container) : Application {
            $application = new Application();
            $this->cleanupDefaultCommands($application);
            return $application;
        });
        return $container;
    }
    private function cleanupDefaultCommands(Application $application) : void
    {
        PrivatesAccessor::propertyClosure($application, 'commands', static function (array $commands) : array {
            // remove default commands, as not needed here
            unset($commands['completion']);
            unset($commands['help']);
            return $commands;
        });
    }
}
