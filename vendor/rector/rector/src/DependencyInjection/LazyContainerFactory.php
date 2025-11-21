<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\DependencyInjection;

use Argtyper202511\RectorPrefix202511\Doctrine\Inflector\Inflector;
use Argtyper202511\RectorPrefix202511\Doctrine\Inflector\Rules\English\InflectorFactory;
use Argtyper202511\RectorPrefix202511\Illuminate\Container\Container;
use Argtyper202511\PhpParser\Lexer;
use Argtyper202511\PHPStan\Analyser\NodeScopeResolver;
use Argtyper202511\PHPStan\Analyser\ScopeFactory;
use Argtyper202511\PHPStan\Parser\Parser;
use Argtyper202511\PHPStan\PhpDoc\TypeNodeResolver;
use Argtyper202511\PHPStan\PhpDocParser\ParserConfig;
use Argtyper202511\PHPStan\Reflection\ReflectionProvider;
use Argtyper202511\Rector\Application\ChangedNodeScopeRefresher;
use Argtyper202511\Rector\Application\FileProcessor;
use Argtyper202511\Rector\Application\Provider\CurrentFileProvider;
use Argtyper202511\Rector\BetterPhpDocParser\Contract\BasePhpDocNodeVisitorInterface;
use Argtyper202511\Rector\BetterPhpDocParser\Contract\PhpDocParser\PhpDocNodeDecoratorInterface;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDocNodeMapper;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDocNodeVisitor\ArrayTypePhpDocNodeVisitor;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDocNodeVisitor\CallableTypePhpDocNodeVisitor;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDocNodeVisitor\IntersectionTypeNodePhpDocNodeVisitor;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDocNodeVisitor\TemplatePhpDocNodeVisitor;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDocNodeVisitor\UnionTypeNodePhpDocNodeVisitor;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDocParser\ArrayItemClassNameDecorator;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDocParser\BetterPhpDocParser;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDocParser\ConstExprClassNameDecorator;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDocParser\DoctrineAnnotationDecorator;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDocParser\PhpDocTagGenericUsesDecorator;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDocParser\StaticDoctrineAnnotationParser;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDocParser\StaticDoctrineAnnotationParser\ArrayParser;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDocParser\StaticDoctrineAnnotationParser\PlainValueParser;
use Argtyper202511\Rector\Caching\Cache;
use Argtyper202511\Rector\Caching\CacheFactory;
use Argtyper202511\Rector\ChangesReporting\Contract\Output\OutputFormatterInterface;
use Argtyper202511\Rector\ChangesReporting\Output\ConsoleOutputFormatter;
use Argtyper202511\Rector\ChangesReporting\Output\GitHubOutputFormatter;
use Argtyper202511\Rector\ChangesReporting\Output\GitlabOutputFormatter;
use Argtyper202511\Rector\ChangesReporting\Output\JsonOutputFormatter;
use Argtyper202511\Rector\ChangesReporting\Output\JUnitOutputFormatter;
use Argtyper202511\Rector\CodingStyle\ClassNameImport\ClassNameImportSkipper;
use Argtyper202511\Rector\CodingStyle\ClassNameImport\ClassNameImportSkipVoter\AliasClassNameImportSkipVoter;
use Argtyper202511\Rector\CodingStyle\ClassNameImport\ClassNameImportSkipVoter\ClassLikeNameClassNameImportSkipVoter;
use Argtyper202511\Rector\CodingStyle\ClassNameImport\ClassNameImportSkipVoter\FullyQualifiedNameClassNameImportSkipVoter;
use Argtyper202511\Rector\CodingStyle\ClassNameImport\ClassNameImportSkipVoter\OriginalNameImportSkipVoter;
use Argtyper202511\Rector\CodingStyle\ClassNameImport\ClassNameImportSkipVoter\ReservedClassNameImportSkipVoter;
use Argtyper202511\Rector\CodingStyle\ClassNameImport\ClassNameImportSkipVoter\ShortClassImportSkipVoter;
use Argtyper202511\Rector\CodingStyle\ClassNameImport\ClassNameImportSkipVoter\UsesClassNameImportSkipVoter;
use Argtyper202511\Rector\CodingStyle\Contract\ClassNameImport\ClassNameImportSkipVoterInterface;
use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\Configuration\ConfigInitializer;
use Argtyper202511\Rector\Configuration\ConfigurationRuleFilter;
use Argtyper202511\Rector\Configuration\OnlyRuleResolver;
use Argtyper202511\Rector\Configuration\RenamedClassesDataCollector;
use Argtyper202511\Rector\Console\Command\CustomRuleCommand;
use Argtyper202511\Rector\Console\Command\ListRulesCommand;
use Argtyper202511\Rector\Console\Command\ProcessCommand;
use Argtyper202511\Rector\Console\Command\SetupCICommand;
use Argtyper202511\Rector\Console\Command\WorkerCommand;
use Argtyper202511\Rector\Console\ConsoleApplication;
use Argtyper202511\Rector\Console\Output\OutputFormatterCollector;
use Argtyper202511\Rector\Console\Style\RectorStyle;
use Argtyper202511\Rector\Console\Style\SymfonyStyleFactory;
use Argtyper202511\Rector\Contract\DependencyInjection\ResetableInterface;
use Argtyper202511\Rector\Contract\Rector\RectorInterface;
use Argtyper202511\Rector\NodeDecorator\CreatedByRuleDecorator;
use Argtyper202511\Rector\NodeNameResolver\Contract\NodeNameResolverInterface;
use Argtyper202511\Rector\NodeNameResolver\NodeNameResolver;
use Argtyper202511\Rector\NodeNameResolver\NodeNameResolver\ClassConstFetchNameResolver;
use Argtyper202511\Rector\NodeNameResolver\NodeNameResolver\ClassConstNameResolver;
use Argtyper202511\Rector\NodeNameResolver\NodeNameResolver\ClassNameResolver;
use Argtyper202511\Rector\NodeNameResolver\NodeNameResolver\FuncCallNameResolver;
use Argtyper202511\Rector\NodeNameResolver\NodeNameResolver\FunctionNameResolver;
use Argtyper202511\Rector\NodeNameResolver\NodeNameResolver\NameNameResolver;
use Argtyper202511\Rector\NodeNameResolver\NodeNameResolver\ParamNameResolver;
use Argtyper202511\Rector\NodeNameResolver\NodeNameResolver\PropertyNameResolver;
use Argtyper202511\Rector\NodeNameResolver\NodeNameResolver\UseNameResolver;
use Argtyper202511\Rector\NodeNameResolver\NodeNameResolver\VariableNameResolver;
use Argtyper202511\Rector\NodeTypeResolver\Contract\NodeTypeResolverInterface;
use Argtyper202511\Rector\NodeTypeResolver\DependencyInjection\PHPStanServicesFactory;
use Argtyper202511\Rector\NodeTypeResolver\NodeTypeResolver;
use Argtyper202511\Rector\NodeTypeResolver\NodeTypeResolver\CastTypeResolver;
use Argtyper202511\Rector\NodeTypeResolver\NodeTypeResolver\ClassAndInterfaceTypeResolver;
use Argtyper202511\Rector\NodeTypeResolver\NodeTypeResolver\ClassConstFetchTypeResolver;
use Argtyper202511\Rector\NodeTypeResolver\NodeTypeResolver\IdentifierTypeResolver;
use Argtyper202511\Rector\NodeTypeResolver\NodeTypeResolver\NameTypeResolver;
use Argtyper202511\Rector\NodeTypeResolver\NodeTypeResolver\NewTypeResolver;
use Argtyper202511\Rector\NodeTypeResolver\NodeTypeResolver\ParamTypeResolver;
use Argtyper202511\Rector\NodeTypeResolver\NodeTypeResolver\PropertyFetchTypeResolver;
use Argtyper202511\Rector\NodeTypeResolver\NodeTypeResolver\PropertyTypeResolver;
use Argtyper202511\Rector\NodeTypeResolver\NodeTypeResolver\ScalarTypeResolver;
use Argtyper202511\Rector\NodeTypeResolver\NodeTypeResolver\StaticCallMethodCallTypeResolver;
use Argtyper202511\Rector\NodeTypeResolver\NodeTypeResolver\TraitTypeResolver;
use Argtyper202511\Rector\NodeTypeResolver\PHPStan\Scope\Contract\NodeVisitor\ScopeResolverNodeVisitorInterface;
use Argtyper202511\Rector\NodeTypeResolver\PHPStan\Scope\NodeVisitor\ArgNodeVisitor;
use Argtyper202511\Rector\NodeTypeResolver\PHPStan\Scope\NodeVisitor\AssignedToNodeVisitor;
use Argtyper202511\Rector\NodeTypeResolver\PHPStan\Scope\NodeVisitor\ByRefReturnNodeVisitor;
use Argtyper202511\Rector\NodeTypeResolver\PHPStan\Scope\NodeVisitor\ByRefVariableNodeVisitor;
use Argtyper202511\Rector\NodeTypeResolver\PHPStan\Scope\NodeVisitor\ContextNodeVisitor;
use Argtyper202511\Rector\NodeTypeResolver\PHPStan\Scope\NodeVisitor\GlobalVariableNodeVisitor;
use Argtyper202511\Rector\NodeTypeResolver\PHPStan\Scope\NodeVisitor\NameNodeVisitor;
use Argtyper202511\Rector\NodeTypeResolver\PHPStan\Scope\NodeVisitor\StaticVariableNodeVisitor;
use Argtyper202511\Rector\NodeTypeResolver\PHPStan\Scope\NodeVisitor\StmtKeyNodeVisitor;
use Argtyper202511\Rector\NodeTypeResolver\PHPStan\Scope\PHPStanNodeScopeResolver;
use Argtyper202511\Rector\NodeTypeResolver\Reflection\BetterReflection\SourceLocatorProvider\DynamicSourceLocatorProvider;
use Argtyper202511\Rector\Php80\AttributeDecorator\DoctrineConverterAttributeDecorator;
use Argtyper202511\Rector\Php80\AttributeDecorator\SensioParamConverterAttributeDecorator;
use Argtyper202511\Rector\Php80\Contract\ConverterAttributeDecoratorInterface;
use Argtyper202511\Rector\Php80\NodeManipulator\AttributeGroupNamedArgumentManipulator;
use Argtyper202511\Rector\PhpAttribute\AnnotationToAttributeMapper;
use Argtyper202511\Rector\PhpAttribute\AnnotationToAttributeMapper\ArrayAnnotationToAttributeMapper;
use Argtyper202511\Rector\PhpAttribute\AnnotationToAttributeMapper\ArrayItemNodeAnnotationToAttributeMapper;
use Argtyper202511\Rector\PhpAttribute\AnnotationToAttributeMapper\ClassConstFetchAnnotationToAttributeMapper;
use Argtyper202511\Rector\PhpAttribute\AnnotationToAttributeMapper\ConstExprNodeAnnotationToAttributeMapper;
use Argtyper202511\Rector\PhpAttribute\AnnotationToAttributeMapper\CurlyListNodeAnnotationToAttributeMapper;
use Argtyper202511\Rector\PhpAttribute\AnnotationToAttributeMapper\DoctrineAnnotationAnnotationToAttributeMapper;
use Argtyper202511\Rector\PhpAttribute\AnnotationToAttributeMapper\StringAnnotationToAttributeMapper;
use Argtyper202511\Rector\PhpAttribute\AnnotationToAttributeMapper\StringNodeAnnotationToAttributeMapper;
use Argtyper202511\Rector\PhpAttribute\Contract\AnnotationToAttributeMapperInterface;
use Argtyper202511\Rector\PhpDocParser\NodeTraverser\SimpleCallableNodeTraverser;
use Argtyper202511\Rector\PhpParser\Comparing\NodeComparator;
use Argtyper202511\Rector\PhpParser\Node\NodeFactory;
use Argtyper202511\Rector\PhpParser\NodeTraverser\RectorNodeTraverser;
use Argtyper202511\Rector\PHPStanStaticTypeMapper\Contract\TypeMapperInterface;
use Argtyper202511\Rector\PHPStanStaticTypeMapper\PHPStanStaticTypeMapper;
use Argtyper202511\Rector\PHPStanStaticTypeMapper\TypeMapper\AccessoryLiteralStringTypeMapper;
use Argtyper202511\Rector\PHPStanStaticTypeMapper\TypeMapper\AccessoryNonEmptyStringTypeMapper;
use Argtyper202511\Rector\PHPStanStaticTypeMapper\TypeMapper\AccessoryNonFalsyStringTypeMapper;
use Argtyper202511\Rector\PHPStanStaticTypeMapper\TypeMapper\AccessoryNumericStringTypeMapper;
use Argtyper202511\Rector\PHPStanStaticTypeMapper\TypeMapper\ArrayTypeMapper;
use Argtyper202511\Rector\PHPStanStaticTypeMapper\TypeMapper\BooleanTypeMapper;
use Argtyper202511\Rector\PHPStanStaticTypeMapper\TypeMapper\CallableTypeMapper;
use Argtyper202511\Rector\PHPStanStaticTypeMapper\TypeMapper\ClassStringTypeMapper;
use Argtyper202511\Rector\PHPStanStaticTypeMapper\TypeMapper\ClosureTypeMapper;
use Argtyper202511\Rector\PHPStanStaticTypeMapper\TypeMapper\ConditionalTypeForParameterMapper;
use Argtyper202511\Rector\PHPStanStaticTypeMapper\TypeMapper\ConditionalTypeMapper;
use Argtyper202511\Rector\PHPStanStaticTypeMapper\TypeMapper\ConstantArrayTypeMapper;
use Argtyper202511\Rector\PHPStanStaticTypeMapper\TypeMapper\FloatTypeMapper;
use Argtyper202511\Rector\PHPStanStaticTypeMapper\TypeMapper\GenericClassStringTypeMapper;
use Argtyper202511\Rector\PHPStanStaticTypeMapper\TypeMapper\HasMethodTypeMapper;
use Argtyper202511\Rector\PHPStanStaticTypeMapper\TypeMapper\HasOffsetTypeMapper;
use Argtyper202511\Rector\PHPStanStaticTypeMapper\TypeMapper\HasOffsetValueTypeTypeMapper;
use Argtyper202511\Rector\PHPStanStaticTypeMapper\TypeMapper\HasPropertyTypeMapper;
use Argtyper202511\Rector\PHPStanStaticTypeMapper\TypeMapper\IntegerTypeMapper;
use Argtyper202511\Rector\PHPStanStaticTypeMapper\TypeMapper\IntersectionTypeMapper;
use Argtyper202511\Rector\PHPStanStaticTypeMapper\TypeMapper\IterableTypeMapper;
use Argtyper202511\Rector\PHPStanStaticTypeMapper\TypeMapper\MixedTypeMapper;
use Argtyper202511\Rector\PHPStanStaticTypeMapper\TypeMapper\NeverTypeMapper;
use Argtyper202511\Rector\PHPStanStaticTypeMapper\TypeMapper\NonEmptyArrayTypeMapper;
use Argtyper202511\Rector\PHPStanStaticTypeMapper\TypeMapper\NullTypeMapper;
use Argtyper202511\Rector\PHPStanStaticTypeMapper\TypeMapper\ObjectTypeMapper;
use Argtyper202511\Rector\PHPStanStaticTypeMapper\TypeMapper\ObjectWithoutClassTypeMapper;
use Argtyper202511\Rector\PHPStanStaticTypeMapper\TypeMapper\OversizedArrayTypeMapper;
use Argtyper202511\Rector\PHPStanStaticTypeMapper\TypeMapper\ParentStaticTypeMapper;
use Argtyper202511\Rector\PHPStanStaticTypeMapper\TypeMapper\ResourceTypeMapper;
use Argtyper202511\Rector\PHPStanStaticTypeMapper\TypeMapper\SelfObjectTypeMapper;
use Argtyper202511\Rector\PHPStanStaticTypeMapper\TypeMapper\StaticTypeMapper;
use Argtyper202511\Rector\PHPStanStaticTypeMapper\TypeMapper\StrictMixedTypeMapper;
use Argtyper202511\Rector\PHPStanStaticTypeMapper\TypeMapper\StringTypeMapper;
use Argtyper202511\Rector\PHPStanStaticTypeMapper\TypeMapper\ThisTypeMapper;
use Argtyper202511\Rector\PHPStanStaticTypeMapper\TypeMapper\TypeWithClassNameTypeMapper;
use Argtyper202511\Rector\PHPStanStaticTypeMapper\TypeMapper\UnionTypeMapper;
use Argtyper202511\Rector\PHPStanStaticTypeMapper\TypeMapper\VoidTypeMapper;
use Argtyper202511\Rector\PostRector\Application\PostFileProcessor;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Rector\Skipper\Skipper\Skipper;
use Argtyper202511\Rector\StaticTypeMapper\Contract\PhpDocParser\PhpDocTypeMapperInterface;
use Argtyper202511\Rector\StaticTypeMapper\Contract\PhpParser\PhpParserNodeMapperInterface;
use Argtyper202511\Rector\StaticTypeMapper\Mapper\PhpParserNodeMapper;
use Argtyper202511\Rector\StaticTypeMapper\PhpDoc\PhpDocTypeMapper;
use Argtyper202511\Rector\StaticTypeMapper\PhpDocParser\IdentifierPhpDocTypeMapper;
use Argtyper202511\Rector\StaticTypeMapper\PhpDocParser\IntersectionPhpDocTypeMapper;
use Argtyper202511\Rector\StaticTypeMapper\PhpDocParser\NullablePhpDocTypeMapper;
use Argtyper202511\Rector\StaticTypeMapper\PhpDocParser\UnionPhpDocTypeMapper;
use Argtyper202511\Rector\StaticTypeMapper\PhpParser\ExprNodeMapper;
use Argtyper202511\Rector\StaticTypeMapper\PhpParser\FullyQualifiedNodeMapper;
use Argtyper202511\Rector\StaticTypeMapper\PhpParser\IdentifierNodeMapper;
use Argtyper202511\Rector\StaticTypeMapper\PhpParser\IntersectionTypeNodeMapper;
use Argtyper202511\Rector\StaticTypeMapper\PhpParser\NameNodeMapper;
use Argtyper202511\Rector\StaticTypeMapper\PhpParser\NullableTypeNodeMapper;
use Argtyper202511\Rector\StaticTypeMapper\PhpParser\StringNodeMapper;
use Argtyper202511\Rector\StaticTypeMapper\PhpParser\UnionTypeNodeMapper;
use Argtyper202511\RectorPrefix202511\Symfony\Component\Console\Application;
use Argtyper202511\RectorPrefix202511\Symfony\Component\Console\Command\Command;
use Argtyper202511\RectorPrefix202511\Symfony\Component\Console\Style\SymfonyStyle;
use Argtyper202511\RectorPrefix202511\Webmozart\Assert\Assert;
final class LazyContainerFactory
{
    /**
     * @var array<class-string<NodeNameResolverInterface>>
     */
    private const NODE_NAME_RESOLVER_CLASSES = [ClassConstFetchNameResolver::class, ClassConstNameResolver::class, ClassNameResolver::class, FuncCallNameResolver::class, FunctionNameResolver::class, NameNameResolver::class, ParamNameResolver::class, PropertyNameResolver::class, UseNameResolver::class, VariableNameResolver::class];
    /**
     * @var array<class-string<BasePhpDocNodeVisitorInterface>>
     */
    private const BASE_PHP_DOC_NODE_VISITORS = [ArrayTypePhpDocNodeVisitor::class, CallableTypePhpDocNodeVisitor::class, IntersectionTypeNodePhpDocNodeVisitor::class, TemplatePhpDocNodeVisitor::class, UnionTypeNodePhpDocNodeVisitor::class];
    /**
     * @var array<class-string<AnnotationToAttributeMapperInterface>>
     */
    private const ANNOTATION_TO_ATTRIBUTE_MAPPER_CLASSES = [ArrayAnnotationToAttributeMapper::class, ArrayItemNodeAnnotationToAttributeMapper::class, ClassConstFetchAnnotationToAttributeMapper::class, ConstExprNodeAnnotationToAttributeMapper::class, CurlyListNodeAnnotationToAttributeMapper::class, DoctrineAnnotationAnnotationToAttributeMapper::class, StringAnnotationToAttributeMapper::class, StringNodeAnnotationToAttributeMapper::class];
    /**
     * @var array<class-string<ScopeResolverNodeVisitorInterface>>
     */
    private const SCOPE_RESOLVER_NODE_VISITOR_CLASSES = [ArgNodeVisitor::class, AssignedToNodeVisitor::class, ByRefReturnNodeVisitor::class, ByRefVariableNodeVisitor::class, ContextNodeVisitor::class, GlobalVariableNodeVisitor::class, NameNodeVisitor::class, StaticVariableNodeVisitor::class, StmtKeyNodeVisitor::class];
    /**
     * @var array<class-string<PhpDocTypeMapperInterface>>
     */
    private const PHPDOC_TYPE_MAPPER_CLASSES = [IdentifierPhpDocTypeMapper::class, IntersectionPhpDocTypeMapper::class, NullablePhpDocTypeMapper::class, UnionPhpDocTypeMapper::class];
    /**
     * @var array<class-string<ClassNameImportSkipVoterInterface>>
     */
    private const CLASS_NAME_IMPORT_SKIPPER_CLASSES = [AliasClassNameImportSkipVoter::class, ClassLikeNameClassNameImportSkipVoter::class, FullyQualifiedNameClassNameImportSkipVoter::class, UsesClassNameImportSkipVoter::class, ReservedClassNameImportSkipVoter::class, ShortClassImportSkipVoter::class, OriginalNameImportSkipVoter::class];
    /**
     * @var array<class-string<TypeMapperInterface>>
     */
    private const TYPE_MAPPER_CLASSES = [AccessoryLiteralStringTypeMapper::class, AccessoryNonEmptyStringTypeMapper::class, AccessoryNonFalsyStringTypeMapper::class, AccessoryNumericStringTypeMapper::class, ConstantArrayTypeMapper::class, ArrayTypeMapper::class, BooleanTypeMapper::class, CallableTypeMapper::class, ClassStringTypeMapper::class, ClosureTypeMapper::class, ConditionalTypeForParameterMapper::class, ConditionalTypeMapper::class, FloatTypeMapper::class, GenericClassStringTypeMapper::class, HasMethodTypeMapper::class, HasOffsetTypeMapper::class, HasOffsetValueTypeTypeMapper::class, HasPropertyTypeMapper::class, IntegerTypeMapper::class, IntersectionTypeMapper::class, IterableTypeMapper::class, MixedTypeMapper::class, NeverTypeMapper::class, NonEmptyArrayTypeMapper::class, NullTypeMapper::class, ObjectTypeMapper::class, ObjectWithoutClassTypeMapper::class, OversizedArrayTypeMapper::class, ParentStaticTypeMapper::class, ResourceTypeMapper::class, SelfObjectTypeMapper::class, StaticTypeMapper::class, StrictMixedTypeMapper::class, StringTypeMapper::class, ThisTypeMapper::class, TypeWithClassNameTypeMapper::class, UnionTypeMapper::class, VoidTypeMapper::class];
    /**
     * @var array<class-string<PhpDocNodeDecoratorInterface>>
     */
    private const PHP_DOC_NODE_DECORATOR_CLASSES = [ConstExprClassNameDecorator::class, DoctrineAnnotationDecorator::class, ArrayItemClassNameDecorator::class, PhpDocTagGenericUsesDecorator::class];
    /**
     * @var array<class-string>
     */
    private const PUBLIC_PHPSTAN_SERVICE_TYPES = [ScopeFactory::class, TypeNodeResolver::class, NodeScopeResolver::class, ReflectionProvider::class];
    /**
     * @var array<class-string<OutputFormatterInterface>>
     */
    private const OUTPUT_FORMATTER_CLASSES = [ConsoleOutputFormatter::class, JsonOutputFormatter::class, GitlabOutputFormatter::class, JUnitOutputFormatter::class, GitHubOutputFormatter::class];
    /**
     * @var array<class-string<NodeTypeResolverInterface>>
     */
    private const NODE_TYPE_RESOLVER_CLASSES = [CastTypeResolver::class, StaticCallMethodCallTypeResolver::class, ClassAndInterfaceTypeResolver::class, IdentifierTypeResolver::class, NameTypeResolver::class, NewTypeResolver::class, ParamTypeResolver::class, PropertyFetchTypeResolver::class, ClassConstFetchTypeResolver::class, PropertyTypeResolver::class, ScalarTypeResolver::class, TraitTypeResolver::class];
    /**
     * @var array<class-string<PhpParserNodeMapperInterface>>
     */
    private const PHP_PARSER_NODE_MAPPER_CLASSES = [FullyQualifiedNodeMapper::class, IdentifierNodeMapper::class, IntersectionTypeNodeMapper::class, NameNodeMapper::class, NullableTypeNodeMapper::class, StringNodeMapper::class, UnionTypeNodeMapper::class, ExprNodeMapper::class];
    /**
     * @var array<class-string<ConverterAttributeDecoratorInterface>>
     */
    private const CONVERTER_ATTRIBUTE_DECORATOR_CLASSES = [SensioParamConverterAttributeDecorator::class, DoctrineConverterAttributeDecorator::class];
    /**
     * @api used as next rectorConfig factory
     */
    public function create(): RectorConfig
    {
        $rectorConfig = new RectorConfig();
        $rectorConfig->import(__DIR__ . '/../../config/config.php');
        $rectorConfig->singleton(Application::class, static function (Container $container): Application {
            $consoleApplication = $container->make(ConsoleApplication::class);
            $commandNamesToHide = ['list', 'completion', 'help', 'worker'];
            foreach ($commandNamesToHide as $commandNameToHide) {
                $commandToHide = $consoleApplication->get($commandNameToHide);
                $commandToHide->setHidden();
            }
            return $consoleApplication;
        });
        $rectorConfig->when(ConsoleApplication::class)->needs('$commands')->giveTagged(Command::class);
        $rectorConfig->singleton(Inflector::class, static function (): Inflector {
            $inflectorFactory = new InflectorFactory();
            return $inflectorFactory->build();
        });
        $rectorConfig->singleton(ConfigurationRuleFilter::class);
        $rectorConfig->singleton(ProcessCommand::class);
        $rectorConfig->singleton(WorkerCommand::class);
        $rectorConfig->singleton(SetupCICommand::class);
        $rectorConfig->singleton(ListRulesCommand::class);
        $rectorConfig->singleton(CustomRuleCommand::class);
        $rectorConfig->when(ListRulesCommand::class)->needs('$rectors')->giveTagged(RectorInterface::class);
        $rectorConfig->when(OnlyRuleResolver::class)->needs('$rectors')->giveTagged(RectorInterface::class);
        $rectorConfig->singleton(FileProcessor::class);
        $rectorConfig->singleton(PostFileProcessor::class);
        $rectorConfig->when(RectorNodeTraverser::class)->needs('$rectors')->giveTagged(RectorInterface::class);
        $rectorConfig->when(ConfigInitializer::class)->needs('$rectors')->giveTagged(RectorInterface::class);
        $rectorConfig->when(ClassNameImportSkipper::class)->needs('$classNameImportSkipVoters')->giveTagged(ClassNameImportSkipVoterInterface::class);
        $rectorConfig->singleton(DynamicSourceLocatorProvider::class, static function (Container $container): DynamicSourceLocatorProvider {
            $phpStanServicesFactory = $container->make(PHPStanServicesFactory::class);
            return $phpStanServicesFactory->createDynamicSourceLocatorProvider();
        });
        // resetables
        $rectorConfig->tag(DynamicSourceLocatorProvider::class, ResetableInterface::class);
        $rectorConfig->tag(RenamedClassesDataCollector::class, ResetableInterface::class);
        // caching
        $rectorConfig->singleton(Cache::class, static function (Container $container): Cache {
            /** @var CacheFactory $cacheFactory */
            $cacheFactory = $container->make(CacheFactory::class);
            return $cacheFactory->create();
        });
        // tagged services
        $rectorConfig->when(BetterPhpDocParser::class)->needs('$phpDocNodeDecorators')->giveTagged(PhpDocNodeDecoratorInterface::class);
        $rectorConfig->afterResolving(ArrayTypeMapper::class, static function (ArrayTypeMapper $arrayTypeMapper, Container $container): void {
            $arrayTypeMapper->autowire($container->make(PHPStanStaticTypeMapper::class));
        });
        $rectorConfig->afterResolving(ConditionalTypeForParameterMapper::class, static function (ConditionalTypeForParameterMapper $conditionalTypeForParameterMapper, Container $container): void {
            $phpStanStaticTypeMapper = $container->make(PHPStanStaticTypeMapper::class);
            $conditionalTypeForParameterMapper->autowire($phpStanStaticTypeMapper);
        });
        $rectorConfig->afterResolving(ConditionalTypeMapper::class, static function (ConditionalTypeMapper $conditionalTypeMapper, Container $container): void {
            $phpStanStaticTypeMapper = $container->make(PHPStanStaticTypeMapper::class);
            $conditionalTypeMapper->autowire($phpStanStaticTypeMapper);
        });
        $rectorConfig->afterResolving(UnionTypeMapper::class, static function (UnionTypeMapper $unionTypeMapper, Container $container): void {
            $phpStanStaticTypeMapper = $container->make(PHPStanStaticTypeMapper::class);
            $unionTypeMapper->autowire($phpStanStaticTypeMapper);
        });
        $rectorConfig->when(PHPStanStaticTypeMapper::class)->needs('$typeMappers')->giveTagged(TypeMapperInterface::class);
        $rectorConfig->when(PhpDocTypeMapper::class)->needs('$phpDocTypeMappers')->giveTagged(PhpDocTypeMapperInterface::class);
        $rectorConfig->when(PhpParserNodeMapper::class)->needs('$phpParserNodeMappers')->giveTagged(PhpParserNodeMapperInterface::class);
        $rectorConfig->when(NodeTypeResolver::class)->needs('$nodeTypeResolvers')->giveTagged(NodeTypeResolverInterface::class);
        // node name resolvers
        $rectorConfig->when(NodeNameResolver::class)->needs('$nodeNameResolvers')->giveTagged(NodeNameResolverInterface::class);
        $rectorConfig->when(AttributeGroupNamedArgumentManipulator::class)->needs('$converterAttributeDecorators')->giveTagged(ConverterAttributeDecoratorInterface::class);
        $this->registerTagged($rectorConfig, self::CONVERTER_ATTRIBUTE_DECORATOR_CLASSES, ConverterAttributeDecoratorInterface::class);
        $rectorConfig->afterResolving(AbstractRector::class, static function (AbstractRector $rector, Container $container): void {
            $rector->autowire($container->get(NodeNameResolver::class), $container->get(NodeTypeResolver::class), $container->get(SimpleCallableNodeTraverser::class), $container->get(NodeFactory::class), $container->get(Skipper::class), $container->get(NodeComparator::class), $container->get(CurrentFileProvider::class), $container->get(CreatedByRuleDecorator::class), $container->get(ChangedNodeScopeRefresher::class));
        });
        $this->registerTagged($rectorConfig, self::PHP_PARSER_NODE_MAPPER_CLASSES, PhpParserNodeMapperInterface::class);
        $this->registerTagged($rectorConfig, self::PHP_DOC_NODE_DECORATOR_CLASSES, PhpDocNodeDecoratorInterface::class);
        $this->registerTagged($rectorConfig, self::BASE_PHP_DOC_NODE_VISITORS, BasePhpDocNodeVisitorInterface::class);
        // PHP 8.0 attributes
        $this->registerTagged($rectorConfig, self::ANNOTATION_TO_ATTRIBUTE_MAPPER_CLASSES, AnnotationToAttributeMapperInterface::class);
        $this->registerTagged($rectorConfig, self::TYPE_MAPPER_CLASSES, TypeMapperInterface::class);
        $this->registerTagged($rectorConfig, self::PHPDOC_TYPE_MAPPER_CLASSES, PhpDocTypeMapperInterface::class);
        $this->registerTagged($rectorConfig, self::NODE_NAME_RESOLVER_CLASSES, NodeNameResolverInterface::class);
        $this->registerTagged($rectorConfig, self::NODE_TYPE_RESOLVER_CLASSES, NodeTypeResolverInterface::class);
        $this->registerTagged($rectorConfig, self::OUTPUT_FORMATTER_CLASSES, OutputFormatterInterface::class);
        $this->registerTagged($rectorConfig, self::BASE_PHP_DOC_NODE_VISITORS, BasePhpDocNodeVisitorInterface::class);
        $this->registerTagged($rectorConfig, self::CLASS_NAME_IMPORT_SKIPPER_CLASSES, ClassNameImportSkipVoterInterface::class);
        $rectorConfig->alias(SymfonyStyle::class, RectorStyle::class);
        $rectorConfig->singleton(SymfonyStyle::class, static function (Container $container): SymfonyStyle {
            $symfonyStyleFactory = $container->make(SymfonyStyleFactory::class);
            return $symfonyStyleFactory->create();
        });
        $rectorConfig->when(AnnotationToAttributeMapper::class)->needs('$annotationToAttributeMappers')->giveTagged(AnnotationToAttributeMapperInterface::class);
        $rectorConfig->when(OutputFormatterCollector::class)->needs('$outputFormatters')->giveTagged(OutputFormatterInterface::class);
        // required-like setter
        $rectorConfig->afterResolving(ArrayAnnotationToAttributeMapper::class, static function (ArrayAnnotationToAttributeMapper $arrayAnnotationToAttributeMapper, Container $container): void {
            $annotationToAttributeMapper = $container->make(AnnotationToAttributeMapper::class);
            $arrayAnnotationToAttributeMapper->autowire($annotationToAttributeMapper);
        });
        $rectorConfig->afterResolving(ArrayItemNodeAnnotationToAttributeMapper::class, static function (ArrayItemNodeAnnotationToAttributeMapper $arrayItemNodeAnnotationToAttributeMapper, Container $container): void {
            $annotationToAttributeMapper = $container->make(AnnotationToAttributeMapper::class);
            $arrayItemNodeAnnotationToAttributeMapper->autowire($annotationToAttributeMapper);
        });
        $rectorConfig->afterResolving(PlainValueParser::class, static function (PlainValueParser $plainValueParser, Container $container): void {
            $plainValueParser->autowire($container->make(StaticDoctrineAnnotationParser::class), $container->make(ArrayParser::class));
        });
        $rectorConfig->afterResolving(CurlyListNodeAnnotationToAttributeMapper::class, static function (CurlyListNodeAnnotationToAttributeMapper $curlyListNodeAnnotationToAttributeMapper, Container $container): void {
            $annotationToAttributeMapper = $container->make(AnnotationToAttributeMapper::class);
            $curlyListNodeAnnotationToAttributeMapper->autowire($annotationToAttributeMapper);
        });
        $rectorConfig->afterResolving(DoctrineAnnotationAnnotationToAttributeMapper::class, static function (DoctrineAnnotationAnnotationToAttributeMapper $doctrineAnnotationAnnotationToAttributeMapper, Container $container): void {
            $annotationToAttributeMapper = $container->make(AnnotationToAttributeMapper::class);
            $doctrineAnnotationAnnotationToAttributeMapper->autowire($annotationToAttributeMapper);
        });
        $rectorConfig->when(PHPStanNodeScopeResolver::class)->needs('$nodeVisitors')->giveTagged(ScopeResolverNodeVisitorInterface::class);
        $this->registerTagged($rectorConfig, self::SCOPE_RESOLVER_NODE_VISITOR_CLASSES, ScopeResolverNodeVisitorInterface::class);
        $this->createPHPStanServices($rectorConfig);
        $rectorConfig->when(PhpDocNodeMapper::class)->needs('$phpDocNodeVisitors')->giveTagged(BasePhpDocNodeVisitorInterface::class);
        // phpdoc-parser
        $rectorConfig->singleton(ParserConfig::class, static function (Container $container): ParserConfig {
            return new ParserConfig(['lines' => \true, 'indexes' => \true, 'comments' => \true]);
        });
        return $rectorConfig;
    }
    /**
     * @param array<class-string> $classes
     * @param class-string $tagInterface
     */
    private function registerTagged(Container $container, array $classes, string $tagInterface): void
    {
        foreach ($classes as $class) {
            Assert::isAOf($class, $tagInterface);
            $container->singleton($class);
            $container->tag($class, $tagInterface);
        }
    }
    private function createPHPStanServices(RectorConfig $rectorConfig): void
    {
        $rectorConfig->singleton(Parser::class, static function (Container $container) {
            $phpStanServicesFactory = $container->make(PHPStanServicesFactory::class);
            return $phpStanServicesFactory->createPHPStanParser();
        });
        $rectorConfig->singleton(Lexer::class, static function (Container $container) {
            $phpStanServicesFactory = $container->make(PHPStanServicesFactory::class);
            return $phpStanServicesFactory->createEmulativeLexer();
        });
        foreach (self::PUBLIC_PHPSTAN_SERVICE_TYPES as $publicPhpstanServiceType) {
            $rectorConfig->singleton($publicPhpstanServiceType, static function (Container $container) use ($publicPhpstanServiceType) {
                $phpStanServicesFactory = $container->make(PHPStanServicesFactory::class);
                return $phpStanServicesFactory->getByType($publicPhpstanServiceType);
            });
        }
    }
}
