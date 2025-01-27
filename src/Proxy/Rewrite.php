<?php

namespace luoyue\aop\Proxy;

use luoyue\aop\Collects\Pointcut;
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

    public function rewrite(string $className, Pointcut $item)
    {
        $traverser = new NodeTraverser();
        $code = file_get_contents($item->getClassFile());
        $ast = $this->parser->parse($code);
        $traverser->addVisitor(new ProxyNodeVisitor($item));
        $newAst = $traverser->traverse($ast);
        $newCode = $this->prettyPrinter->prettyPrintFile($newAst);
        $proxyFile = $this->getProxyPath() . $item->getProxyFile();
        file_put_contents($proxyFile, $newCode);
        return $proxyFile;
    }

    private function getProxyPath()
    {
        $path = base_path() . config('plugin.luoyue.aop.app.proxy_path', '/runtime/aopCache/proxyClasses') . DIRECTORY_SEPARATOR;
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }
        return $path;
    }

    /**
     * @param $className
     */
    protected function getProxyFilePath($className): string
    {
        return $this->config->getPath() . '/' . str_replace('\\', '_', $className) . '.proxy.php';
    }
}
