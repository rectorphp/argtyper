<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\PHPStan\Scope;

use Argtyper202511\PHPStan\Analyser\MutatingScope;
use Argtyper202511\PHPStan\Analyser\ScopeContext;
use Argtyper202511\PHPStan\Analyser\ScopeFactory as PHPStanScopeFactory;
final class ScopeFactory
{
    /**
     * @readonly
     * @var PHPStanScopeFactory
     */
    private $phpStanScopeFactory;
    public function __construct(PHPStanScopeFactory $phpStanScopeFactory)
    {
        $this->phpStanScopeFactory = $phpStanScopeFactory;
    }
    public function createFromFile(string $filePath): MutatingScope
    {
        $scopeContext = ScopeContext::create($filePath);
        return $this->phpStanScopeFactory->create($scopeContext);
    }
}
