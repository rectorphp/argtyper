<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\NodeTypeResolver\DependencyInjection;

use Argtyper202511\PhpParser\Lexer;
use Argtyper202511\PHPStan\Analyser\NodeScopeResolver;
use Argtyper202511\PHPStan\Analyser\ScopeFactory;
use Argtyper202511\PHPStan\DependencyInjection\Container;
use Argtyper202511\PHPStan\DependencyInjection\ContainerFactory;
use Argtyper202511\PHPStan\File\FileHelper;
use Argtyper202511\PHPStan\Parser\Parser;
use Argtyper202511\PHPStan\PhpDoc\TypeNodeResolver;
use Argtyper202511\PHPStan\Reflection\ReflectionProvider;
use Argtyper202511\Rector\Configuration\Option;
use Argtyper202511\Rector\Configuration\Parameter\SimpleParameterProvider;
use Argtyper202511\Rector\NodeTypeResolver\Reflection\BetterReflection\SourceLocatorProvider\DynamicSourceLocatorProvider;
use Argtyper202511\RectorPrefix202511\Symfony\Component\Console\Input\ArrayInput;
use Argtyper202511\RectorPrefix202511\Symfony\Component\Console\Output\ConsoleOutput;
use Argtyper202511\RectorPrefix202511\Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;
use Argtyper202511\RectorPrefix202511\Webmozart\Assert\Assert;
/**
 * Factory so Symfony app can use services from PHPStan container
 *
 * @see \Rector\NodeTypeResolver\DependencyInjection\PHPStanServicesFactory
 */
final class PHPStanServicesFactory
{
    /**
     * @var string
     */
    private const INVALID_BLEEDING_EDGE_PATH_MESSAGE = <<<MESSAGE_ERROR
'%s, use full path bleedingEdge.neon config, eg:

includes:
    - phar://vendor/phpstan/phpstan/phpstan.phar/conf/bleedingEdge.neon

in your included phpstan configuration.

MESSAGE_ERROR;
    /**
     * @readonly
     * @var \PHPStan\DependencyInjection\Container
     */
    private $container;
    public function __construct()
    {
        $containerFactory = new ContainerFactory(\getcwd());
        $additionalConfigFiles = $this->resolveAdditionalConfigFiles();
        try {
            $this->container = $containerFactory->create(SimpleParameterProvider::provideStringParameter(Option::CONTAINER_CACHE_DIRECTORY), $additionalConfigFiles, []);
        } catch (Throwable $throwable) {
            if ($throwable->getMessage() === "File 'phar://phpstan.phar/conf/bleedingEdge.neon' is missing or is not readable.") {
                $symfonyStyle = new SymfonyStyle(new ArrayInput([]), new ConsoleOutput());
                $symfonyStyle->error(\str_replace("\r\n", "\n", \sprintf(self::INVALID_BLEEDING_EDGE_PATH_MESSAGE, $throwable->getMessage())));
                exit(-1);
            }
            throw $throwable;
        }
    }
    /**
     * @api
     */
    public function createReflectionProvider() : ReflectionProvider
    {
        return $this->container->getByType(ReflectionProvider::class);
    }
    /**
     * @api
     */
    public function createEmulativeLexer() : Lexer
    {
        return $this->container->getService('currentPhpVersionLexer');
    }
    /**
     * @api
     */
    public function createPHPStanParser() : Parser
    {
        return $this->container->getService('currentPhpVersionRichParser');
    }
    /**
     * @api
     */
    public function createNodeScopeResolver() : NodeScopeResolver
    {
        return $this->container->getByType(NodeScopeResolver::class);
    }
    /**
     * @api
     */
    public function createScopeFactory() : ScopeFactory
    {
        return $this->container->getByType(ScopeFactory::class);
    }
    /**
     * @template TObject as Object
     *
     * @param class-string<TObject> $type
     * @return TObject
     */
    public function getByType(string $type) : object
    {
        return $this->container->getByType($type);
    }
    /**
     * @api
     */
    public function createFileHelper() : FileHelper
    {
        return $this->container->getByType(FileHelper::class);
    }
    /**
     * @api
     */
    public function createTypeNodeResolver() : TypeNodeResolver
    {
        return $this->container->getByType(TypeNodeResolver::class);
    }
    /**
     * @api
     */
    public function createDynamicSourceLocatorProvider() : DynamicSourceLocatorProvider
    {
        return $this->container->getByType(DynamicSourceLocatorProvider::class);
    }
    /**
     * @return string[]
     */
    private function resolveAdditionalConfigFiles() : array
    {
        $additionalConfigFiles = [];
        if (SimpleParameterProvider::hasParameter(Option::PHPSTAN_FOR_RECTOR_PATHS)) {
            $paths = SimpleParameterProvider::provideArrayParameter(Option::PHPSTAN_FOR_RECTOR_PATHS);
            foreach ($paths as $path) {
                Assert::string($path);
                $additionalConfigFiles[] = $path;
            }
        }
        $additionalConfigFiles[] = __DIR__ . '/../../../config/phpstan/static-reflection.neon';
        $additionalConfigFiles[] = __DIR__ . '/../../../config/phpstan/better-infer.neon';
        $additionalConfigFiles[] = __DIR__ . '/../../../config/phpstan/parser.neon';
        return \array_filter($additionalConfigFiles, \Closure::fromCallable('file_exists'));
    }
}
