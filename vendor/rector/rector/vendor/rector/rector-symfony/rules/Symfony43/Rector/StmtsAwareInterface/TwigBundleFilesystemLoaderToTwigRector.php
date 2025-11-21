<?php

declare (strict_types=1);
namespace Rector\Symfony\Symfony43\Rector\StmtsAwareInterface;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Arg;
use Argtyper202511\PhpParser\Node\Expr\Assign;
use Argtyper202511\PhpParser\Node\Expr\MethodCall;
use Argtyper202511\PhpParser\Node\Expr\New_;
use Argtyper202511\PhpParser\Node\Name\FullyQualified;
use Argtyper202511\PhpParser\Node\Stmt\Expression;
use Argtyper202511\PHPStan\Type\ObjectType;
use Rector\Contract\PhpParser\Node\StmtsAwareInterface;
use Rector\Rector\AbstractRector;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Symfony\Tests\Symfony43\Rector\StmtsAwareInterface\TwigBundleFilesystemLoaderToTwigRector\TwigBundleFilesystemLoaderToTwigRectorTest
 */
final class TwigBundleFilesystemLoaderToTwigRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change TwigBundle FilesystemLoader to native one', [new CodeSample(<<<'CODE_SAMPLE'
use Symfony\Bundle\TwigBundle\Loader\FilesystemLoader;
use Symfony\Bundle\FrameworkBundle\Templating\Loader\TemplateLocator;
use Symfony\Bundle\FrameworkBundle\Templating\TemplateNameParser;

$filesystemLoader = new FilesystemLoader(new TemplateLocator(), new TemplateParser());
$filesystemLoader->addPath(__DIR__ . '/some-directory');
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Twig\Loader\FilesystemLoader;

$fileSystemLoader = new FilesystemLoader([__DIR__ . '/some-directory']);
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [StmtsAwareInterface::class];
    }
    /**
     * @param StmtsAwareInterface $node
     */
    public function refactor(Node $node): ?Node
    {
        $filesystemLoaderNew = $this->resolveFileSystemLoaderNew($node);
        if (!$filesystemLoaderNew instanceof New_) {
            return null;
        }
        $collectedPathExprs = [];
        foreach ((array) $node->stmts as $key => $stmt) {
            if (!$stmt instanceof Expression) {
                continue;
            }
            if (!$stmt->expr instanceof MethodCall) {
                continue;
            }
            $methodCall = $stmt->expr;
            if (!$this->isName($methodCall->name, 'addPath')) {
                continue;
            }
            $collectedPathExprs[] = $methodCall->getArgs()[0];
            unset($node->stmts[$key]);
        }
        $filesystemLoaderNew->class = new FullyQualified('Argtyper202511\Twig\Loader\FilesystemLoader');
        $array = $this->nodeFactory->createArray($collectedPathExprs);
        $filesystemLoaderNew->args = [new Arg($array)];
        return $node;
    }
    private function resolveFileSystemLoaderNew(StmtsAwareInterface $stmtsAware): ?New_
    {
        foreach ((array) $stmtsAware->stmts as $stmt) {
            if (!$stmt instanceof Expression) {
                continue;
            }
            if (!$stmt->expr instanceof Assign) {
                continue;
            }
            $assign = $stmt->expr;
            if (!$assign->expr instanceof New_) {
                continue;
            }
            $new = $assign->expr;
            if (!$this->isObjectType($new, new ObjectType('Argtyper202511\Symfony\Bundle\TwigBundle\Loader\FilesystemLoader'))) {
                continue;
            }
            return $new;
        }
        return null;
    }
}
