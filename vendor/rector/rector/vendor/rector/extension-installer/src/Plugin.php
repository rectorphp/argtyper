<?php

declare (strict_types=1);
namespace Rector\RectorInstaller;

use Argtyper202511\Composer\Composer;
use Argtyper202511\Composer\EventDispatcher\EventSubscriberInterface;
use Argtyper202511\Composer\IO\IOInterface;
use Argtyper202511\Composer\Plugin\PluginInterface;
use Argtyper202511\Composer\Script\Event;
use Argtyper202511\Composer\Script\ScriptEvents;
final class Plugin implements PluginInterface, EventSubscriberInterface
{
    public function activate(Composer $composer, IOInterface $io): void
    {
    }
    public function deactivate(Composer $composer, IOInterface $io): void
    {
    }
    public function uninstall(Composer $composer, IOInterface $io): void
    {
    }
    public function process(Event $event): void
    {
        $io = $event->getIO();
        $composer = $event->getComposer();
        $installationManager = $composer->getInstallationManager();
        $repositoryManager = $composer->getRepositoryManager();
        $localRepository = $repositoryManager->getLocalRepository();
        $configurationFile = __DIR__ . '/GeneratedConfig.php';
        $pluginInstaller = new \Rector\RectorInstaller\PluginInstaller(new \Rector\RectorInstaller\LocalFilesystem(), $localRepository, $io, $installationManager, new \Argtyper202511\Composer\Util\Filesystem(), $configurationFile);
        $pluginInstaller->install();
    }
    /**
     * @return array<string, string>
     */
    public static function getSubscribedEvents(): array
    {
        return [ScriptEvents::POST_INSTALL_CMD => 'process', ScriptEvents::POST_UPDATE_CMD => 'process'];
    }
}
