<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Application;

use Argtyper202511\RectorPrefix202511\Nette\Utils\FileSystem;
use Argtyper202511\PHPStan\AnalysedCodeException;
use Argtyper202511\PHPStan\Parser\ParserErrorsException;
use Argtyper202511\Rector\Caching\Detector\ChangedFilesDetector;
use Argtyper202511\Rector\ChangesReporting\ValueObjectFactory\ErrorFactory;
use Argtyper202511\Rector\ChangesReporting\ValueObjectFactory\FileDiffFactory;
use Argtyper202511\Rector\Exception\ShouldNotHappenException;
use Argtyper202511\Rector\FileSystem\FilePathHelper;
use Argtyper202511\Rector\NodeTypeResolver\NodeScopeAndMetadataDecorator;
use Argtyper202511\Rector\PhpParser\NodeTraverser\RectorNodeTraverser;
use Argtyper202511\Rector\PhpParser\Parser\ParserErrors;
use Argtyper202511\Rector\PhpParser\Parser\RectorParser;
use Argtyper202511\Rector\PhpParser\Printer\BetterStandardPrinter;
use Argtyper202511\Rector\PostRector\Application\PostFileProcessor;
use Argtyper202511\Rector\Testing\PHPUnit\StaticPHPUnitEnvironment;
use Argtyper202511\Rector\ValueObject\Application\File;
use Argtyper202511\Rector\ValueObject\Configuration;
use Argtyper202511\Rector\ValueObject\Error\SystemError;
use Argtyper202511\Rector\ValueObject\FileProcessResult;
use Argtyper202511\RectorPrefix202511\Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;
final class FileProcessor
{
    /**
     * @readonly
     * @var \Rector\PhpParser\Printer\BetterStandardPrinter
     */
    private $betterStandardPrinter;
    /**
     * @readonly
     * @var \Rector\PhpParser\NodeTraverser\RectorNodeTraverser
     */
    private $rectorNodeTraverser;
    /**
     * @readonly
     * @var \RectorPrefix202511\Symfony\Component\Console\Style\SymfonyStyle
     */
    private $symfonyStyle;
    /**
     * @readonly
     * @var \Rector\ChangesReporting\ValueObjectFactory\FileDiffFactory
     */
    private $fileDiffFactory;
    /**
     * @readonly
     * @var \Rector\Caching\Detector\ChangedFilesDetector
     */
    private $changedFilesDetector;
    /**
     * @readonly
     * @var \Rector\ChangesReporting\ValueObjectFactory\ErrorFactory
     */
    private $errorFactory;
    /**
     * @readonly
     * @var \Rector\FileSystem\FilePathHelper
     */
    private $filePathHelper;
    /**
     * @readonly
     * @var \Rector\PostRector\Application\PostFileProcessor
     */
    private $postFileProcessor;
    /**
     * @readonly
     * @var \Rector\PhpParser\Parser\RectorParser
     */
    private $rectorParser;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeScopeAndMetadataDecorator
     */
    private $nodeScopeAndMetadataDecorator;
    public function __construct(BetterStandardPrinter $betterStandardPrinter, RectorNodeTraverser $rectorNodeTraverser, SymfonyStyle $symfonyStyle, FileDiffFactory $fileDiffFactory, ChangedFilesDetector $changedFilesDetector, ErrorFactory $errorFactory, FilePathHelper $filePathHelper, PostFileProcessor $postFileProcessor, RectorParser $rectorParser, NodeScopeAndMetadataDecorator $nodeScopeAndMetadataDecorator)
    {
        $this->betterStandardPrinter = $betterStandardPrinter;
        $this->rectorNodeTraverser = $rectorNodeTraverser;
        $this->symfonyStyle = $symfonyStyle;
        $this->fileDiffFactory = $fileDiffFactory;
        $this->changedFilesDetector = $changedFilesDetector;
        $this->errorFactory = $errorFactory;
        $this->filePathHelper = $filePathHelper;
        $this->postFileProcessor = $postFileProcessor;
        $this->rectorParser = $rectorParser;
        $this->nodeScopeAndMetadataDecorator = $nodeScopeAndMetadataDecorator;
    }
    public function processFile(File $file, Configuration $configuration) : FileProcessResult
    {
        // 1. parse files to nodes
        $parsingSystemError = $this->parseFileAndDecorateNodes($file);
        if ($parsingSystemError instanceof SystemError) {
            // we cannot process this file as the parsing and type resolving itself went wrong
            return new FileProcessResult([$parsingSystemError], null, \false);
        }
        $fileHasChanged = \false;
        $filePath = $file->getFilePath();
        do {
            $file->changeHasChanged(\false);
            // 1. change nodes with Rector Rules
            $newStmts = $this->rectorNodeTraverser->traverse($file->getNewStmts());
            // 2. apply post rectors
            $postNewStmts = $this->postFileProcessor->traverse($newStmts, $file);
            // 3. this is needed for new tokens added in "afterTraverse()"
            $file->changeNewStmts($postNewStmts);
            // 4. print to file or string
            // important to detect if file has changed
            $this->printFile($file, $configuration, $filePath);
            // no change in current iteration, stop
            if (!$file->hasChanged()) {
                break;
            }
            $fileHasChanged = \true;
        } while (\true);
        // 5. add as cacheable if not changed at all
        if (!$fileHasChanged) {
            $this->changedFilesDetector->addCacheableFile($filePath);
        } else {
            // when changed, set final status changed to true
            // to ensure it make sense to verify in next process when needed
            $file->changeHasChanged(\true);
        }
        $rectorWithLineChanges = $file->getRectorWithLineChanges();
        if ($file->hasChanged() || $rectorWithLineChanges !== []) {
            $currentFileDiff = $this->fileDiffFactory->createFileDiffWithLineChanges($configuration->shouldShowDiffs(), $file, $file->getOriginalFileContent(), $file->getFileContent(), $file->getRectorWithLineChanges());
            $file->setFileDiff($currentFileDiff);
        }
        return new FileProcessResult([], $file->getFileDiff(), $file->hasChanged());
    }
    private function parseFileAndDecorateNodes(File $file) : ?SystemError
    {
        try {
            try {
                $this->parseFileNodes($file);
            } catch (ParserErrorsException $exception) {
                $this->parseFileNodes($file, \false);
            }
        } catch (ShouldNotHappenException $shouldNotHappenException) {
            throw $shouldNotHappenException;
        } catch (AnalysedCodeException $analysedCodeException) {
            // inform about missing classes in tests
            if (StaticPHPUnitEnvironment::isPHPUnitRun()) {
                throw $analysedCodeException;
            }
            return $this->errorFactory->createAutoloadError($analysedCodeException, $file->getFilePath());
        } catch (Throwable $throwable) {
            if ($this->symfonyStyle->isVerbose() || StaticPHPUnitEnvironment::isPHPUnitRun()) {
                throw $throwable;
            }
            $relativeFilePath = $this->filePathHelper->relativePath($file->getFilePath());
            if ($throwable instanceof ParserErrorsException) {
                $throwable = new ParserErrors($throwable);
            }
            return new SystemError($throwable->getMessage(), $relativeFilePath, $throwable->getLine());
        }
        return null;
    }
    private function printFile(File $file, Configuration $configuration, string $filePath) : void
    {
        // only save to string first, no need to print to file when not needed
        $newContent = $this->betterStandardPrinter->printFormatPreserving($file->getNewStmts(), $file->getOldStmts(), $file->getOldTokens());
        // change file content early to make $file->hasChanged() based on new content
        $file->changeFileContent($newContent);
        if ($configuration->isDryRun()) {
            return;
        }
        if (!$file->hasChanged()) {
            return;
        }
        FileSystem::write($filePath, $newContent, null);
    }
    private function parseFileNodes(File $file, bool $forNewestSupportedVersion = \true) : void
    {
        // store tokens by original file content, so we don't have to print them right now
        $stmtsAndTokens = $this->rectorParser->parseFileContentToStmtsAndTokens($file->getOriginalFileContent(), $forNewestSupportedVersion);
        $oldStmts = $stmtsAndTokens->getStmts();
        $oldTokens = $stmtsAndTokens->getTokens();
        $newStmts = $this->nodeScopeAndMetadataDecorator->decorateNodesFromFile($file->getFilePath(), $oldStmts);
        $file->hydrateStmtsAndTokens($newStmts, $oldStmts, $oldTokens);
    }
}
