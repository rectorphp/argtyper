<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Bridge;

use Argtyper202511\Rector\Doctrine\Set\SetProvider\DoctrineSetProvider;
use Argtyper202511\Rector\PHPUnit\Set\SetProvider\PHPUnitSetProvider;
use Argtyper202511\Rector\Set\Contract\SetInterface;
use Argtyper202511\Rector\Set\Contract\SetProviderInterface;
use Argtyper202511\Rector\Set\SetProvider\CoreSetProvider;
use Argtyper202511\Rector\Set\SetProvider\PHPSetProvider;
use Argtyper202511\Rector\Set\ValueObject\ComposerTriggeredSet;
use Argtyper202511\Rector\Symfony\Set\SetProvider\Symfony3SetProvider;
use Argtyper202511\Rector\Symfony\Set\SetProvider\Symfony4SetProvider;
use Argtyper202511\Rector\Symfony\Set\SetProvider\Symfony5SetProvider;
use Argtyper202511\Rector\Symfony\Set\SetProvider\Symfony6SetProvider;
use Argtyper202511\Rector\Symfony\Set\SetProvider\Symfony7SetProvider;
use Argtyper202511\Rector\Symfony\Set\SetProvider\SymfonySetProvider;
use Argtyper202511\Rector\Symfony\Set\SetProvider\TwigSetProvider;
/**
 * @api
 *
 * Utils class to ease building bridges by 3rd-party tools
 */
final class SetProviderCollector
{
    /**
     * @var SetProviderInterface[]
     * @readonly
     */
    private $setProviders;
    /**
     * @param SetProviderInterface[] $extraSetProviders
     */
    public function __construct(array $extraSetProviders = [])
    {
        $setProviders = [
            // register all known set providers here
            new PHPSetProvider(),
            new CoreSetProvider(),
            new PHPUnitSetProvider(),
            new SymfonySetProvider(),
            new Symfony3SetProvider(),
            new Symfony4SetProvider(),
            new Symfony5SetProvider(),
            new Symfony6SetProvider(),
            new Symfony7SetProvider(),
            new DoctrineSetProvider(),
            new TwigSetProvider(),
        ];
        $this->setProviders = array_merge($setProviders, $extraSetProviders);
    }
    /**
     * @return array<SetProviderInterface>
     */
    public function provide(): array
    {
        return $this->setProviders;
    }
    /**
     * @return array<SetInterface>
     */
    public function provideSets(): array
    {
        $sets = [];
        foreach ($this->setProviders as $setProvider) {
            $sets = array_merge($sets, $setProvider->provide());
        }
        return $sets;
    }
    /**
     * @return array<ComposerTriggeredSet>
     */
    public function provideComposerTriggeredSets(): array
    {
        return array_filter($this->provideSets(), function (SetInterface $set): bool {
            return $set instanceof ComposerTriggeredSet;
        });
    }
}
