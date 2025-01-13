<?php

namespace luoyue\aop;

use luoyue\aop\Attributes\Aspect;
use luoyue\aop\exception\ConfigException;
use luoyue\aop\interfaces\ProxyInterface;
use ReflectionClass;
use SplFileInfo;
use support\Container;

class Config
{

    private array $aspectsClasses = [];

    private string $path = BASE_PATH . '/runtime/Aop/aopProxyClasses';

    public function __construct(private array $config = [])
    {
        $this->parse();
    }

    public function parse(): void
    {
        if (!is_array($this->config['scans'] ?? '') || !is_array($this->config['aspect'] ?? '')) {
            throw new ConfigException('Aop config err: config with key : scans');
        }
        array_map([$this, 'recursiveScan'], $this->config['scans']);
        array_map([$this, 'mergeAspectClass'], $this->config['aspect']);
        if (!file_exists($this->path)) {
            mkdir($this->path, 0777, true);
        }
    }

    public function getAspectsClasses(): array
    {
        return array_unique($this->aspectsClasses);
    }

    public function getPath(): string
    {
        return $this->path;
    }
    
    /**
     * 递归扫描目录下的aspect.
     * @param $dir
     */
    private function recursiveScan(string $dir): void
    {
        if (!file_exists($dir)) {
            throw new ConfigException('dir not exists');
        }
        $dirIterator = new \RecursiveDirectoryIterator($dir);
        $iterator = new \RecursiveIteratorIterator($dirIterator);
        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() == 'php') {
                // 根据文件路径获取类名
                $className = str_replace(['./', '../', '/'], ['', '', '\\'], substr($file->getPathname(), 0, -4));
                $this->mergeAspectClass($className);
            }
        }
    }

    /**
     * @param $class
     */
    private function mergeAspectClass(string $class): void
    {
        if (!class_exists($class)) {
            return;
        }
        $reflectionClass = new ReflectionClass($class);
        if (!in_array(ProxyInterface::class, $reflectionClass->getInterfaceNames())) {
            return;
        }
        if(!empty($reflectionClass->getAttributes(Aspect::class))) {
            $this->aspectsClasses[] = $class;
        }
    }

    private function getValue($key): mixed
    {
        return $this->config[$key] ?? null;
    }
}
