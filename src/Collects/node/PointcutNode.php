<?php

namespace luoyue\aop\Collects\node;

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

    /** @var array $pointcutMethod 切入点方法集合 */
    private array $pointcutMethod; // method1 => aspectdata[], method2 => aspectdata[], method3 => aspectdata[]

    public function __construct(string $pointcutClass, string $classFile)
    {
        $this->pointcutClass = $pointcutClass;
        $this->classFile = $classFile;
    }

    /**
     * 获取切入点文件路径(原文件)
     * @return string
     */
    public function getClassFile(): string
    {
        return $this->classFile;
    }

    /**
     * 获取切入点代理文件名
     * @return string
     */
    public function getProxyFile(): string
    {
        return $this->proxyFile ??= str_replace('\\', '_', $this->pointcutClass) . '.proxy.php';
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
    public function addPointcutMethod(string $method, AspectNode $aspectNode): void
    {
        $this->pointcutMethod[$method][] = $aspectNode;
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
        $aspects = $this->pointcutMethod[$methodName] ?? [];
        return array_map(fn (AspectNode $item) => $item->getAdviceClosure(), $aspects);
    }
}