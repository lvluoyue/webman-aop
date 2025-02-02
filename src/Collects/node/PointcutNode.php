<?php

namespace Luoyue\aop\Collects\node;

use Luoyue\aop\AopBootstrap;
use Luoyue\aop\Collects\AspectManager;
use SplPriorityQueue;

/**
 * 切入点节点
 */
class PointcutNode
{
    /** @var string $pointcutClass 切入点类名 */
    private string $pointcutClass;

    /** @var string $classFile 切入点文件路径 */
    private string $classFile;

    /** @var string $proxyFile 切入点代理文件名 */
    private string $proxyFile;

    /** @var array<string, SplPriorityQueue> $pointcutMethod 切入点方法集合 */
    private array $pointcutMethod; // method1 => aspectdata[], method2 => aspectdata[], method3 => aspectdata[]

    public function __construct(string $pointcutClass)
    {
        $this->pointcutClass = $pointcutClass;
    }

    /**
     * 获取切入点文件路径(原文件)
     * @return string
     */
    public function getClassFile(): string
    {
        return $this->classFile ??= AopBootstrap::getComposerClassLoader()->findFile($this->pointcutClass);
    }

    /**
     * 获取切入点代理文件名
     * @return string
     */
    public function getProxyFile(bool $path = false): string
    {
        $this->proxyFile ??= str_replace('\\', '_', $this->pointcutClass) . '.proxy.php';
        return $path ? $this->getProxyPath() . DIRECTORY_SEPARATOR . $this->proxyFile : $this->proxyFile;
    }

    public function getProxyClassName(bool $namespace = false): string
    {
        $proxyClassName = $this->pointcutClass;
        if (str_contains($this->pointcutClass, '\\')) {
            $exploded = explode('\\', $this->pointcutClass);
            $proxyClassName = end($exploded);
        }
        $namespaceName = '';
        if ($namespace) {
            unset($exploded[count($exploded) - 1]);
            $namespaceName = implode('\\', $exploded) . '\\';
         }
        return $namespaceName . $proxyClassName . '_' . crc32($proxyClassName);
    }

    /**
     * 添加切入点方法
     * @param string $method
     * @param AspectNode $aspectNode
     * @return void
     */
    public function addPointcutMethod(string $method, AspectNode $aspectNode): static
    {
        $this->pointcutMethod[$method] ??= new SplPriorityQueue();
        $this->pointcutMethod[$method]->insert($aspectNode, $aspectNode->getPriority());
        return $this;
    }

    /**
     * 判断方法名是否需要重写
     * @param string $name
     * @return bool
     */
    public function shouldRewriteMethod(string $methodName): bool
    {
        return (bool)($this->pointcutMethod[$methodName] ?? false);
    }

    /**
     * 获取切面闭包集合
     * @param string $methodName 方法名
     * @return \Closure[] 切面闭包集合
     */
    public function getAdviceClosure(string $methodName): array
    {
        if(AspectManager::has($this->pointcutClass, $methodName)) {
            return AspectManager::get($this->pointcutClass, $methodName);
        }
        $aspects = $this->pointcutMethod[$methodName] ?? false;
        if (!$aspects) {
            return [];
        }
        while ($aspects->valid()) {
            /** @var AspectNode $aspect */
            $aspect = $aspects->current();
            AspectManager::insert($this->pointcutClass, $methodName, $aspect->getAdviceClosure());
            $aspects->next();
        }
        return AspectManager::get($this->pointcutClass, $methodName);
    }

    /**
     * 获取代理文件路径
     * @return string
     */
    private function getProxyPath()
    {
        $path = base_path() . config('plugin.luoyue.aop.app.proxy_path', '/runtime/cache/aop') . DIRECTORY_SEPARATOR;
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }
        return $path;
    }

}