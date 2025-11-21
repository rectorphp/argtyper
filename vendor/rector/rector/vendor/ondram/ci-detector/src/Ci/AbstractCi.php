<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511\OndraM\CiDetector\Ci;

use Argtyper202511\RectorPrefix202511\OndraM\CiDetector\Env;
/**
 * Unified adapter to retrieve environment variables from current continuous integration server
 */
abstract class AbstractCi implements CiInterface
{
    /**
     * @var \RectorPrefix202511\OndraM\CiDetector\Env
     */
    protected $env;
    public function __construct(Env $env)
    {
        $this->env = $env;
    }
    public function describe(): array
    {
        return ['ci-name' => $this->getCiName(), 'build-number' => $this->getBuildNumber(), 'build-url' => $this->getBuildUrl(), 'commit' => $this->getCommit(), 'branch' => $this->getBranch(), 'target-branch' => $this->getTargetBranch(), 'repository-name' => $this->getRepositoryName(), 'repository-url' => $this->getRepositoryUrl(), 'is-pull-request' => $this->isPullRequest()->describe()];
    }
}
