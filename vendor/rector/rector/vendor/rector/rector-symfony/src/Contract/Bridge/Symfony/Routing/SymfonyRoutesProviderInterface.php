<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Symfony\Contract\Bridge\Symfony\Routing;

use Argtyper202511\Rector\Symfony\ValueObject\SymfonyRouteMetadata;
interface SymfonyRoutesProviderInterface
{
    /**
     * @return SymfonyRouteMetadata[]
     */
    public function provide(): array;
}
