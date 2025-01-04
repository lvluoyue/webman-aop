<?php
/**
 * This file is part of monda-worker.
 *
 * @contact  mondagroup_php@163.com
 *
 */
namespace luoyue\aop\Proxy;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

/**
 * Class ProxyClassVisitor.
 */
class ProxyClassVisitor extends NodeVisitorAbstract
{
    private string $proxyClassName;

    public function __construct(string $proxyClassName)
    {
        if (str_contains($proxyClassName, '\\')) {
            $exploded = explode('\\', $proxyClassName);
            $proxyClassName = end($exploded);
        }
        $this->proxyClassName = $proxyClassName;
    }

    public function leaveNode(Node $node): ?Node
    {
        // Rewrite the class name and extends the original class.
        if ($node instanceof Node\Stmt\Class_ && ! $node->isAnonymous()) {
            $node->extends = new Node\Name($node->name->name);
            $node->name = new Node\Identifier($this->proxyClassName);
            return $node;
        }
        return null;
    }
}
