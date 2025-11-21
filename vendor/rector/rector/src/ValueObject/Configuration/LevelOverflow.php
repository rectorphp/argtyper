<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\ValueObject\Configuration;

final class LevelOverflow
{
    /**
     * @readonly
     * @var string
     */
    private $configurationName;
    /**
     * @readonly
     * @var int
     */
    private $level;
    /**
     * @readonly
     * @var int
     */
    private $ruleCount;
    /**
     * @readonly
     * @var string
     */
    private $suggestedRuleset;
    /**
     * @readonly
     * @var string
     */
    private $suggestedSetListConstant;
    public function __construct(string $configurationName, int $level, int $ruleCount, string $suggestedRuleset, string $suggestedSetListConstant)
    {
        $this->configurationName = $configurationName;
        $this->level = $level;
        $this->ruleCount = $ruleCount;
        $this->suggestedRuleset = $suggestedRuleset;
        $this->suggestedSetListConstant = $suggestedSetListConstant;
    }
    public function getConfigurationName() : string
    {
        return $this->configurationName;
    }
    public function getLevel() : int
    {
        return $this->level;
    }
    public function getRuleCount() : int
    {
        return $this->ruleCount;
    }
    public function getSuggestedRuleset() : string
    {
        return $this->suggestedRuleset;
    }
    public function getSuggestedSetListConstant() : string
    {
        return $this->suggestedSetListConstant;
    }
}
