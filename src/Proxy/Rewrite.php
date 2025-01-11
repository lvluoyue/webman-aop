<?php

namespace luoyue\aop\Proxy;

use luoyue\aop\Collects\ProxyCollects;
use luoyue\aop\Config;
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

    public function __construct(private Config $config, private ProxyCollects $proxyCollects)
    {
        $this->parser = (new ParserFactory())->createForHostVersion();
        $this->prettyPrinter = new Standard();
    }

    public function rewrite()
    {
        foreach ($this->proxyCollects->getClassMap() as $className => &$item) {
            $traverser = new NodeTraverser();
            $code = file_get_contents($item['filePath']);
            $ast = $this->parser->parse($code);
            $proxyClassName = $className . '_' . crc32($className);
            $traverser->addVisitor(new ProxyClassVisitor($proxyClassName));
            $traverser->addVisitor(new ProxyNodeVisitor($this->proxyCollects));
            $newAst = $traverser->traverse($ast);
            $newCode = $this->prettyPrinter->prettyPrintFile($newAst);
            $filePath = $this->getProxyFilePath($className);
            file_put_contents($filePath, $newCode);
            $this->proxyCollects->setNewPath($className, $filePath);
            $this->proxyCollects->setProxyClassName($className, $proxyClassName, $filePath);
        }
    }

    /**
     * @param $className
     */
    protected function getProxyFilePath($className): string
    {
        return $this->config->getPath() . '/' . str_replace('\\', '_', $className) . '.proxy.php';
    }
}
