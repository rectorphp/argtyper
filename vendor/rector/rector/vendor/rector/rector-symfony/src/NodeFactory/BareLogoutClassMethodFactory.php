<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Symfony\NodeFactory;

use Argtyper202511\PhpParser\Node\Expr\Variable;
use Argtyper202511\PhpParser\Node\Identifier;
use Argtyper202511\PhpParser\Node\Name\FullyQualified;
use Argtyper202511\PhpParser\Node\Param;
use Argtyper202511\PhpParser\Node\Stmt\ClassMethod;
use Argtyper202511\Rector\Php\PhpVersionProvider;
use Argtyper202511\Rector\PhpParser\Node\NodeFactory;
use Argtyper202511\Rector\ValueObject\PhpVersionFeature;
final class BareLogoutClassMethodFactory
{
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\NodeFactory
     */
    private $nodeFactory;
    /**
     * @readonly
     * @var \Rector\Php\PhpVersionProvider
     */
    private $phpVersionProvider;
    public function __construct(NodeFactory $nodeFactory, PhpVersionProvider $phpVersionProvider)
    {
        $this->nodeFactory = $nodeFactory;
        $this->phpVersionProvider = $phpVersionProvider;
    }
    public function create() : ClassMethod
    {
        $classMethod = $this->nodeFactory->createPublicMethod('onLogout');
        $variable = new Variable('logoutEvent');
        $classMethod->params[] = $this->createLogoutEventParam($variable);
        if ($this->phpVersionProvider->isAtLeastPhpVersion(PhpVersionFeature::VOID_TYPE)) {
            $classMethod->returnType = new Identifier('void');
        }
        return $classMethod;
    }
    private function createLogoutEventParam(Variable $variable) : Param
    {
        $param = new Param($variable);
        $param->type = new FullyQualified('Argtyper202511\\Symfony\\Component\\Security\\Http\\Event\\LogoutEvent');
        return $param;
    }
}
