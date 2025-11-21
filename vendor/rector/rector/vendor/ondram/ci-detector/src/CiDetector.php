<?php

declare (strict_types=1);
namespace RectorPrefix202511\OndraM\CiDetector;

use RectorPrefix202511\OndraM\CiDetector\Ci\CiInterface;
use RectorPrefix202511\OndraM\CiDetector\Exception\CiNotDetectedException;
/**
 * Unified way to get environment variables from current continuous integration server
 */
class CiDetector implements \RectorPrefix202511\OndraM\CiDetector\CiDetectorInterface
{
    public const CI_APPVEYOR = 'AppVeyor';
    public const CI_AWS_CODEBUILD = 'AWS CodeBuild';
    public const CI_AZURE_PIPELINES = 'Azure Pipelines';
    public const CI_BAMBOO = 'Bamboo';
    public const CI_BITBUCKET_PIPELINES = 'Bitbucket Pipelines';
    public const CI_BUDDY = 'Buddy';
    public const CI_CIRCLE = 'CircleCI';
    public const CI_CODESHIP = 'Codeship';
    public const CI_CONTINUOUSPHP = 'continuousphp';
    public const CI_DRONE = 'drone';
    public const CI_GITHUB_ACTIONS = 'GitHub Actions';
    public const CI_GITLAB = 'GitLab';
    public const CI_JENKINS = 'Jenkins';
    public const CI_SOURCEHUT = 'SourceHut';
    public const CI_TEAMCITY = 'TeamCity';
    public const CI_TRAVIS = 'Travis CI';
    /**
     * @deprecated Will be removed in next major version
     */
    public const CI_WERCKER = 'Wercker';
    /**
     * @var \RectorPrefix202511\OndraM\CiDetector\Env
     */
    private $environment;
    final public function __construct()
    {
        $this->environment = new \RectorPrefix202511\OndraM\CiDetector\Env();
    }
    public static function fromEnvironment(\RectorPrefix202511\OndraM\CiDetector\Env $environment): self
    {
        $detector = new static();
        $detector->environment = $environment;
        return $detector;
    }
    public function isCiDetected(): bool
    {
        $ciServer = $this->detectCurrentCiServer();
        return $ciServer !== null;
    }
    public function detect(): CiInterface
    {
        $ciServer = $this->detectCurrentCiServer();
        if ($ciServer === null) {
            throw new CiNotDetectedException('No CI server detected in current environment');
        }
        return $ciServer;
    }
    /**
     * @return string[]
     */
    protected function getCiServers(): array
    {
        return [\RectorPrefix202511\OndraM\CiDetector\Ci\AppVeyor::class, \RectorPrefix202511\OndraM\CiDetector\Ci\AwsCodeBuild::class, \RectorPrefix202511\OndraM\CiDetector\Ci\AzurePipelines::class, \RectorPrefix202511\OndraM\CiDetector\Ci\Bamboo::class, \RectorPrefix202511\OndraM\CiDetector\Ci\BitbucketPipelines::class, \RectorPrefix202511\OndraM\CiDetector\Ci\Buddy::class, \RectorPrefix202511\OndraM\CiDetector\Ci\Circle::class, \RectorPrefix202511\OndraM\CiDetector\Ci\Codeship::class, \RectorPrefix202511\OndraM\CiDetector\Ci\Continuousphp::class, \RectorPrefix202511\OndraM\CiDetector\Ci\Drone::class, \RectorPrefix202511\OndraM\CiDetector\Ci\GitHubActions::class, \RectorPrefix202511\OndraM\CiDetector\Ci\GitLab::class, \RectorPrefix202511\OndraM\CiDetector\Ci\Jenkins::class, \RectorPrefix202511\OndraM\CiDetector\Ci\SourceHut::class, \RectorPrefix202511\OndraM\CiDetector\Ci\TeamCity::class, \RectorPrefix202511\OndraM\CiDetector\Ci\Travis::class, \RectorPrefix202511\OndraM\CiDetector\Ci\Wercker::class];
    }
    protected function detectCurrentCiServer(): ?CiInterface
    {
        $ciServers = $this->getCiServers();
        foreach ($ciServers as $ciClass) {
            $callback = [$ciClass, 'isDetected'];
            if (is_callable($callback) && $callback($this->environment)) {
                return new $ciClass($this->environment);
            }
        }
        return null;
    }
}
