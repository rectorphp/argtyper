<?php

declare (strict_types=1);
namespace Rector\Symfony\Symfony73\Rector\Class_;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr\ConstFetch;
use Argtyper202511\PhpParser\Node\Expr\Variable;
use Argtyper202511\PhpParser\Node\Name;
use Argtyper202511\PhpParser\Node\Name\FullyQualified;
use Argtyper202511\PhpParser\Node\NullableType;
use Argtyper202511\PhpParser\Node\Param;
use Argtyper202511\PhpParser\Node\Stmt\Class_;
use Rector\Rector\AbstractRector;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Symfony\Tests\Symfony73\Rector\Class_\AddVoteArgumentToVoteOnAttributeRector\AddVoteArgumentToVoteOnAttributeRectorTest
 */
final class AddVoteArgumentToVoteOnAttributeRector extends AbstractRector
{
    private const VOTE_INTERFACE = 'Argtyper202511\Symfony\Component\Security\Core\Authorization\Voter\VoterInterface';
    private const VOTER_CLASS = 'Argtyper202511\Symfony\Component\Security\Core\Authorization\Voter\Voter';
    private const VOTE_CLASS = 'Argtyper202511\Symfony\Component\Security\Core\Authorization\Voter\Vote';
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Adds a new `$voter` argument in protected function `voteOnAttribute(string $attribute, $subject, TokenInterface $token, ?Vote $vote = null): bool`', [new CodeSample(<<<'CODE_SAMPLE'
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class MyVoter extends Voter
{
    protected function supports(string $attribute, mixed $subject): bool
    {
        return true;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        return true;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class MyVoter extends Voter
{
    protected function supports(string $attribute, mixed $subject): bool
    {
        return true;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token, ?\Symfony\Component\Security\Core\Authorization\Voter\Vote $vote = null): bool
    {
        return true;
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        $classMethod = null;
        if ($node->extends !== null && $this->isName($node->extends, self::VOTER_CLASS)) {
            $classMethod = $node->getMethod('voteOnAttribute');
        }
        if ($classMethod === null) {
            foreach ($node->implements as $implement) {
                if ($this->isName($implement, self::VOTE_INTERFACE)) {
                    $classMethod = $node->getMethod('vote');
                    break;
                }
            }
        }
        if ($classMethod === null) {
            return null;
        }
        if (count($classMethod->params) !== 3) {
            return null;
        }
        $classMethod->params[] = new Param(new Variable('vote'), new ConstFetch(new Name('null')), new NullableType(new FullyQualified(self::VOTE_CLASS)));
        return $node;
    }
}
