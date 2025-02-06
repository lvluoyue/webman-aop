<?php

namespace Luoyue\aop\Proxy;

use Luoyue\aop\Collects\node\PointcutNode;
use PhpParser\NodeTraverser;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;

/**
 * Class RewriteClass.
 */
class Rewrite
{
    private Parser $parser;

    private Standard $prettyPrinter;

    public function __construct()
    {
        $this->parser = (new ParserFactory())->createForHostVersion();
        $this->prettyPrinter = new Standard();
    }

    /**
     * 重写代理类.
     */
    public function rewrite(PointcutNode $item): void
    {
        $traverser = new NodeTraverser();
        // 获取原始代码
        $code = file_get_contents($item->getClassFile());
        $ast = $this->parser->parse($code);
        $traverser->addVisitor(new ProxyNodeVisitor($item));
        $newAst = $traverser->traverse($ast);
        $newCode = $this->prettyPrinter->prettyPrintFile($newAst);
        $proxyFile = $item->getProxyFile(true);
        file_put_contents($proxyFile, $newCode);
    }
}
