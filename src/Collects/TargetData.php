<?php

namespace luoyue\aop\Collects;

class TargetData
{
    private string $targetClass;
    private string $classFile;
    private string $proxyFile;
    private array $targetMethod; // method1 => aspectdata[], method2 => aspectdata[], method3 => aspectdata[]

    public function __construct(string $targetClass, string $classFile)
    {
        $this->targetClass = $targetClass;
        $this->classFile = $classFile;
    }

    public function getClassFile()
    {
        return $this->classFile;
    }

    public function getProxyFile()
    {
        return $this->proxyFile ??= str_replace('\\', '_', $this->targetClass) . '.proxy.php';
    }
    public function getProxyClassName(bool $namespace = false)
    {
        $proxyClassName = $this->targetClass;
        if (str_contains($this->targetClass, '\\')) {
            $exploded = explode('\\', $this->targetClass);
            $proxyClassName = end($exploded);
        }
        $namespaceName = '';
        if ($namespace) {
            unset($exploded[count($exploded) - 1]);
            $namespaceName = implode('\\', $exploded) . '\\';
         }
        return $namespaceName . $proxyClassName . '_' . crc32($proxyClassName);
    }

    public function addTargetClass(string $method, AspectData $targetMethod)
    {
        $this->targetMethod[$method][] = $targetMethod;
    }

    public function shouldRewriteMethod(string $name): bool
    {
        return (bool)($this->targetMethod[$name] ?? false);
    }

    /**
     * @param string $method 方法名
     * @return \Closure[] 切面闭包集合
     */
    public function getAspectsClosure(string $method): array
    {
        $aspects = $this->targetMethod[$method] ?? [];
        return array_map(fn (AspectData $item) => $item->getAspectClosure(), $aspects);
    }
}