<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Console\Command;

use Argtyper202511\RectorPrefix202511\Clue\React\NDJson\Decoder;
use Argtyper202511\RectorPrefix202511\Clue\React\NDJson\Encoder;
use Argtyper202511\RectorPrefix202511\React\EventLoop\StreamSelectLoop;
use Argtyper202511\RectorPrefix202511\React\Socket\ConnectionInterface;
use Argtyper202511\RectorPrefix202511\React\Socket\TcpConnector;
use Argtyper202511\Rector\Application\ApplicationFileProcessor;
use Argtyper202511\Rector\Autoloading\AdditionalAutoloader;
use Argtyper202511\Rector\Configuration\ConfigurationFactory;
use Argtyper202511\Rector\Configuration\ConfigurationRuleFilter;
use Argtyper202511\Rector\Console\ProcessConfigureDecorator;
use Argtyper202511\Rector\Parallel\ValueObject\Bridge;
use Argtyper202511\Rector\StaticReflection\DynamicSourceLocatorDecorator;
use Argtyper202511\Rector\Util\MemoryLimiter;
use Argtyper202511\Rector\ValueObject\Configuration;
use Argtyper202511\Rector\ValueObject\Error\SystemError;
use Argtyper202511\RectorPrefix202511\Symfony\Component\Console\Command\Command;
use Argtyper202511\RectorPrefix202511\Symfony\Component\Console\Input\InputInterface;
use Argtyper202511\RectorPrefix202511\Symfony\Component\Console\Output\OutputInterface;
use Argtyper202511\RectorPrefix202511\Symplify\EasyParallel\Enum\Action;
use Argtyper202511\RectorPrefix202511\Symplify\EasyParallel\Enum\ReactCommand;
use Argtyper202511\RectorPrefix202511\Symplify\EasyParallel\Enum\ReactEvent;
use Throwable;
use Argtyper202511\RectorPrefix202511\Webmozart\Assert\Assert;
/**
 * Inspired at: https://github.com/phpstan/phpstan-src/commit/9124c66dcc55a222e21b1717ba5f60771f7dda92
 * https://github.com/phpstan/phpstan-src/blob/c471c7b050e0929daf432288770de673b394a983/src/Command/WorkerCommand.php
 *
 * ↓↓↓
 * https://github.com/phpstan/phpstan-src/commit/b84acd2e3eadf66189a64fdbc6dd18ff76323f67#diff-7f625777f1ce5384046df08abffd6c911cfbb1cfc8fcb2bdeaf78f337689e3e2
 */
final class WorkerCommand extends Command
{
    /**
     * @readonly
     * @var \Rector\Autoloading\AdditionalAutoloader
     */
    private $additionalAutoloader;
    /**
     * @readonly
     * @var \Rector\StaticReflection\DynamicSourceLocatorDecorator
     */
    private $dynamicSourceLocatorDecorator;
    /**
     * @readonly
     * @var \Rector\Application\ApplicationFileProcessor
     */
    private $applicationFileProcessor;
    /**
     * @readonly
     * @var \Rector\Util\MemoryLimiter
     */
    private $memoryLimiter;
    /**
     * @readonly
     * @var \Rector\Configuration\ConfigurationFactory
     */
    private $configurationFactory;
    /**
     * @readonly
     * @var \Rector\Configuration\ConfigurationRuleFilter
     */
    private $configurationRuleFilter;
    /**
     * @var string
     */
    private const RESULT = 'result';
    public function __construct(AdditionalAutoloader $additionalAutoloader, DynamicSourceLocatorDecorator $dynamicSourceLocatorDecorator, ApplicationFileProcessor $applicationFileProcessor, MemoryLimiter $memoryLimiter, ConfigurationFactory $configurationFactory, ConfigurationRuleFilter $configurationRuleFilter)
    {
        $this->additionalAutoloader = $additionalAutoloader;
        $this->dynamicSourceLocatorDecorator = $dynamicSourceLocatorDecorator;
        $this->applicationFileProcessor = $applicationFileProcessor;
        $this->memoryLimiter = $memoryLimiter;
        $this->configurationFactory = $configurationFactory;
        $this->configurationRuleFilter = $configurationRuleFilter;
        parent::__construct();
    }
    protected function configure() : void
    {
        $this->setName('worker');
        $this->setDescription('[INTERNAL] Support for parallel process');
        ProcessConfigureDecorator::decorate($this);
        parent::configure();
    }
    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $configuration = $this->configurationFactory->createFromInput($input);
        $this->memoryLimiter->adjust($configuration);
        $this->configurationRuleFilter->setConfiguration($configuration);
        $streamSelectLoop = new StreamSelectLoop();
        $parallelIdentifier = $configuration->getParallelIdentifier();
        $tcpConnector = new TcpConnector($streamSelectLoop);
        $promise = $tcpConnector->connect('127.0.0.1:' . $configuration->getParallelPort());
        $promise->then(function (ConnectionInterface $connection) use($parallelIdentifier, $configuration, $output) : void {
            $inDecoder = new Decoder($connection, \true, 512, \JSON_INVALID_UTF8_IGNORE);
            $outEncoder = new Encoder($connection, \JSON_INVALID_UTF8_IGNORE);
            $outEncoder->write([ReactCommand::ACTION => Action::HELLO, ReactCommand::IDENTIFIER => $parallelIdentifier]);
            $this->runWorker($outEncoder, $inDecoder, $configuration, $output);
        });
        $streamSelectLoop->run();
        return self::SUCCESS;
    }
    private function runWorker(Encoder $encoder, Decoder $decoder, Configuration $configuration, OutputInterface $output) : void
    {
        $this->additionalAutoloader->autoloadPaths();
        $this->dynamicSourceLocatorDecorator->addPaths($configuration->getPaths());
        if ($configuration->isDebug()) {
            $preFileCallback = static function (string $filePath) use($output) : void {
                $output->writeln($filePath);
            };
        } else {
            $preFileCallback = null;
        }
        // 1. handle system error
        $handleErrorCallback = static function (Throwable $throwable) use($encoder) : void {
            $systemError = new SystemError($throwable->getMessage(), $throwable->getFile(), $throwable->getLine());
            $encoder->write([ReactCommand::ACTION => Action::RESULT, self::RESULT => [Bridge::SYSTEM_ERRORS => [$systemError], Bridge::FILES_COUNT => 0, Bridge::SYSTEM_ERRORS_COUNT => 1]]);
            $encoder->end();
        };
        $encoder->on(ReactEvent::ERROR, $handleErrorCallback);
        // 2. collect diffs + errors from file processor
        $decoder->on(ReactEvent::DATA, function (array $json) use($preFileCallback, $encoder, $configuration) : void {
            $action = $json[ReactCommand::ACTION];
            if ($action !== Action::MAIN) {
                return;
            }
            /** @var string[] $filePaths */
            $filePaths = $json[Bridge::FILES] ?? [];
            Assert::notEmpty($filePaths);
            $processResult = $this->applicationFileProcessor->processFiles($filePaths, $configuration, $preFileCallback);
            /**
             * this invokes all listeners listening $decoder->on(...) @see \Symplify\EasyParallel\Enum\ReactEvent::DATA
             */
            $encoder->write([ReactCommand::ACTION => Action::RESULT, self::RESULT => [Bridge::FILE_DIFFS => $processResult->getFileDiffs(), Bridge::FILES_COUNT => \count($filePaths), Bridge::SYSTEM_ERRORS => $processResult->getSystemErrors(), Bridge::SYSTEM_ERRORS_COUNT => \count($processResult->getSystemErrors()), Bridge::TOTAL_CHANGED => $processResult->getTotalChanged()]]);
        });
        $decoder->on(ReactEvent::ERROR, $handleErrorCallback);
    }
}
