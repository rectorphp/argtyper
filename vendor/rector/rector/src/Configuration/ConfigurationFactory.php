<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Configuration;

use Argtyper202511\Rector\ChangesReporting\Output\ConsoleOutputFormatter;
use Argtyper202511\Rector\Configuration\Parameter\SimpleParameterProvider;
use Argtyper202511\Rector\ValueObject\Configuration;
use Argtyper202511\RectorPrefix202511\Symfony\Component\Console\Input\InputInterface;
use Argtyper202511\RectorPrefix202511\Symfony\Component\Console\Style\SymfonyStyle;
/**
 * @see \Rector\Tests\Configuration\ConfigurationFactoryTest
 */
final class ConfigurationFactory
{
    /**
     * @readonly
     * @var \RectorPrefix202511\Symfony\Component\Console\Style\SymfonyStyle
     */
    private $symfonyStyle;
    /**
     * @readonly
     * @var \Rector\Configuration\OnlyRuleResolver
     */
    private $onlyRuleResolver;
    public function __construct(SymfonyStyle $symfonyStyle, \Argtyper202511\Rector\Configuration\OnlyRuleResolver $onlyRuleResolver)
    {
        $this->symfonyStyle = $symfonyStyle;
        $this->onlyRuleResolver = $onlyRuleResolver;
    }
    /**
     * @api used in tests
     * @param string[] $paths
     */
    public function createForTests(array $paths): Configuration
    {
        $fileExtensions = SimpleParameterProvider::provideArrayParameter(\Argtyper202511\Rector\Configuration\Option::FILE_EXTENSIONS);
        return new Configuration(\false, \true, \false, ConsoleOutputFormatter::NAME, $fileExtensions, $paths, \true, null, null, \false, null, \false, \false);
    }
    /**
     * Needs to run in the start of the life cycle, since the rest of workflow uses it.
     */
    public function createFromInput(InputInterface $input): Configuration
    {
        $isDryRun = (bool) $input->getOption(\Argtyper202511\Rector\Configuration\Option::DRY_RUN);
        $shouldClearCache = (bool) $input->getOption(\Argtyper202511\Rector\Configuration\Option::CLEAR_CACHE);
        $outputFormat = (string) $input->getOption(\Argtyper202511\Rector\Configuration\Option::OUTPUT_FORMAT);
        $showProgressBar = $this->shouldShowProgressBar($input, $outputFormat);
        $showDiffs = $this->shouldShowDiffs($input);
        $paths = $this->resolvePaths($input);
        $fileExtensions = SimpleParameterProvider::provideArrayParameter(\Argtyper202511\Rector\Configuration\Option::FILE_EXTENSIONS);
        // filter rule and path
        $onlyRule = $input->getOption(\Argtyper202511\Rector\Configuration\Option::ONLY);
        if ($onlyRule !== null) {
            $onlyRule = $this->onlyRuleResolver->resolve($onlyRule);
        }
        $onlySuffix = $input->getOption(\Argtyper202511\Rector\Configuration\Option::ONLY_SUFFIX);
        $isParallel = SimpleParameterProvider::provideBoolParameter(\Argtyper202511\Rector\Configuration\Option::PARALLEL);
        $parallelPort = (string) $input->getOption(\Argtyper202511\Rector\Configuration\Option::PARALLEL_PORT);
        $parallelIdentifier = (string) $input->getOption(\Argtyper202511\Rector\Configuration\Option::PARALLEL_IDENTIFIER);
        $isDebug = (bool) $input->getOption(\Argtyper202511\Rector\Configuration\Option::DEBUG);
        // using debug disables parallel, so emitting exception is straightforward and easier to debug
        if ($isDebug) {
            $isParallel = \false;
        }
        $memoryLimit = $this->resolveMemoryLimit($input);
        $isReportingWithRealPath = SimpleParameterProvider::provideBoolParameter(\Argtyper202511\Rector\Configuration\Option::ABSOLUTE_FILE_PATH);
        $levelOverflows = SimpleParameterProvider::provideArrayParameter(\Argtyper202511\Rector\Configuration\Option::LEVEL_OVERFLOWS);
        return new Configuration($isDryRun, $showProgressBar, $shouldClearCache, $outputFormat, $fileExtensions, $paths, $showDiffs, $parallelPort, $parallelIdentifier, $isParallel, $memoryLimit, $isDebug, $isReportingWithRealPath, $onlyRule, $onlySuffix, $levelOverflows);
    }
    private function shouldShowProgressBar(InputInterface $input, string $outputFormat): bool
    {
        $noProgressBar = (bool) $input->getOption(\Argtyper202511\Rector\Configuration\Option::NO_PROGRESS_BAR);
        if ($noProgressBar) {
            return \false;
        }
        if ($this->symfonyStyle->isVerbose()) {
            return \false;
        }
        return $outputFormat === ConsoleOutputFormatter::NAME;
    }
    private function shouldShowDiffs(InputInterface $input): bool
    {
        $noDiffs = (bool) $input->getOption(\Argtyper202511\Rector\Configuration\Option::NO_DIFFS);
        if ($noDiffs) {
            return \false;
        }
        // fallback to parameter
        return !SimpleParameterProvider::provideBoolParameter(\Argtyper202511\Rector\Configuration\Option::NO_DIFFS, \false);
    }
    /**
     * @return string[]|mixed[]
     */
    private function resolvePaths(InputInterface $input): array
    {
        $commandLinePaths = (array) $input->getArgument(\Argtyper202511\Rector\Configuration\Option::SOURCE);
        // give priority to command line
        if ($commandLinePaths !== []) {
            $this->setFilesWithoutExtensionParameter($commandLinePaths);
            return $commandLinePaths;
        }
        // fallback to parameter
        $configPaths = SimpleParameterProvider::provideArrayParameter(\Argtyper202511\Rector\Configuration\Option::PATHS);
        $this->setFilesWithoutExtensionParameter($configPaths);
        return $configPaths;
    }
    /**
     * @param string[] $paths
     */
    private function setFilesWithoutExtensionParameter(array $paths): void
    {
        foreach ($paths as $path) {
            if (is_file($path) && pathinfo($path, \PATHINFO_EXTENSION) === '') {
                $path = realpath($path);
                if ($path === \false) {
                    continue;
                }
                SimpleParameterProvider::addParameter(\Argtyper202511\Rector\Configuration\Option::FILES_WITHOUT_EXTENSION, $path);
            }
        }
    }
    private function resolveMemoryLimit(InputInterface $input): ?string
    {
        $memoryLimit = $input->getOption(\Argtyper202511\Rector\Configuration\Option::MEMORY_LIMIT);
        if ($memoryLimit !== null) {
            return (string) $memoryLimit;
        }
        if (!SimpleParameterProvider::hasParameter(\Argtyper202511\Rector\Configuration\Option::MEMORY_LIMIT)) {
            return null;
        }
        return SimpleParameterProvider::provideStringParameter(\Argtyper202511\Rector\Configuration\Option::MEMORY_LIMIT);
    }
}
