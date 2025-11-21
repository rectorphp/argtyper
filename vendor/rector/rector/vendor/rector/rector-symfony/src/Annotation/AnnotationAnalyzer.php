<?php

declare (strict_types=1);
namespace Rector\Symfony\Annotation;

use Argtyper202511\PhpParser\Node\Stmt\Class_;
use Rector\Doctrine\NodeAnalyzer\AttrinationFinder;
use Rector\Symfony\Enum\SymfonyAnnotation;
final class AnnotationAnalyzer
{
    /**
     * @readonly
     * @var \Rector\Doctrine\NodeAnalyzer\AttrinationFinder
     */
    private $attrinationFinder;
    public function __construct(AttrinationFinder $attrinationFinder)
    {
        $this->attrinationFinder = $attrinationFinder;
    }
    public function hasClassMethodWithTemplateAnnotation(Class_ $class): bool
    {
        if ($this->attrinationFinder->hasByOne($class, SymfonyAnnotation::TEMPLATE)) {
            return \true;
        }
        foreach ($class->getMethods() as $classMethod) {
            if ($this->attrinationFinder->hasByOne($classMethod, SymfonyAnnotation::TEMPLATE)) {
                return \true;
            }
        }
        return \false;
    }
}
