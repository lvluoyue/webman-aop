<?php

namespace Luoyue\aop;

use LinFly\Annotation\Util\AnnotationUtil;
use Luoyue\aop\Collects\AspectCollects;
use Luoyue\aop\Collects\node\AspectNode;
use Luoyue\aop\Collects\node\PointcutNode;
use Luoyue\aop\Collects\ProxyCollects;
use Luoyue\aop\enum\AdviceTypeEnum;
use Luoyue\aop\Proxy\Rewrite;
use ReflectionClass;
use ReflectionException;
use SplFileInfo;
use SplPriorityQueue;
use support\Container;

class Aspect
{
    private static Aspect $instance;

    private SplPriorityQueue $queue;

    private AspectCollects $aspectCollects;

    private array $config;
    private ProxyCollects $proxyCollects;

    private static bool $isInit = false;

    private function __construct()
    {
        $this->queue = new class extends SplPriorityQueue {
            public function compare($priority1, $priority2): int
            {
                if ($priority1 === $priority2) {
                    return 0;
                }
                // 数字优先
                if (\is_int($priority1) && !\is_int($priority2)) {
                    return 1;
                }
                if (!\is_int($priority1) && \is_int($priority2)) {
                    return -1;
                }

                return $priority1 <=> $priority2;
            }
        };
        $this->aspectCollects = new AspectCollects();
        $this->proxyCollects = new ProxyCollects();
        $this->config = config('plugin.luoyue.aop.app');
    }

    public static function getInstance(): self
    {
        return self::$instance ??= new self();
    }

    public function getAspectCollects(): AspectCollects
    {
        return $this->aspectCollects;
    }

    public function getProxyCollects(): ProxyCollects
    {
        return $this->proxyCollects;
    }

    public function addAspect(string $aspectClass, ?int $priority = null): static
    {
        if ($priority === null) {
            $priority = $aspectClass;
        }
        $this->queue->insert($aspectClass, $priority);

        return $this;
    }

    public function scan(): static
    {
        $this->scanAnnotations();
        foreach ($this->config['scans'] as $scan) {
            $this->scanPointcut($scan);
        }

        //        $this->scanProxy();
        return $this;
    }

    /**
     * 执行切面注解扫描逻辑.
     * @throws ReflectionException
     */
    private function scanAnnotations(): void
    {
        foreach ($this->queue as $aspectClass) {
            $reflectionClass = new ReflectionClass($aspectClass);
            // 遍历切面类方法
            foreach ($reflectionClass->getMethods() as $method) {
                $reflectionAttributes = AopUtils::filterAttributes($method, AdviceTypeEnum::getAnnotationNames());
                // 过滤非切面注解
                foreach ($reflectionAttributes as $annotation) {
                    // 获取方法名
                    $methodName = $method->getName();
                    // 获取枚举对象
                    $adviceType = AdviceTypeEnum::tryFrom($annotation->getName());
                    // 获取切入点正则表达式数组
                    $matches = array_map([AopUtils::class, 'getMatchesClasses'], (array) $annotation->getArguments()[0]);
                    // 将切面节点添加到切面收集器中
                    $this->aspectCollects->addAspects(new AspectNode($aspectClass, $methodName, $adviceType, $matches));
                }
            }
        }
    }

    /**
     * 扫描切入点目录.
     * @throws ReflectionException
     */
    private function scanPointcut(string $dir): void
    {
        $dirIterator = new \RecursiveDirectoryIterator(base_path($dir));
        $iterator = new \RecursiveIteratorIterator($dirIterator);
        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                // 扫描文件中的类名集合
                $classNames = AnnotationUtil::getAllClassesInFile($file->getPathname());
                // 遍历切入点
                foreach ($this->aspectCollects->getAspectsClasses() as $data) {
                    // 遍历文件类名
                    foreach ($classNames as $className) {
                        /** @var AspectNode $aspect */
                        [$aspect, $class, $matchesMethod] = array_values($data);
                        // 匹配切入点
                        if (preg_match("#^{$class}$#", $className)) {
                            $reflectionClass = new ReflectionClass($className);
                            if ($reflectionClass->isInterface() || $reflectionClass->isEnum()) {// 判断是否是class
                                continue;
                            }
                            $pointcutNode = $this->proxyCollects->getPointcutNode($className);
                            foreach ($reflectionClass->getMethods() as $method) {
                                if (preg_match("#^{$matchesMethod}$#", $method->getName())) {
                                    $pointcutNode->addPointcutMethod($method->getName(), $aspect);
                                }
                            }
                            // 将切入节点添加到切面节点
                            $aspect->addMatchesPointcut($pointcutNode);
                        }
                    }
                }
            }
        }
    }

    public function reload(): void
    {
        $rewrite = new Rewrite();
        /** @var PointcutNode[] $map */
        foreach ($this->proxyCollects->getPointcutMap() as $map) {
            [$className, $pointcutNode] = $map;
            $proxyClass = $pointcutNode->getProxyClassName(true);
            $rewrite->rewrite($pointcutNode);
            $container = Container::instance();
            AopBootstrap::getComposerClassLoader()->addClassMap([$proxyClass => $pointcutNode->getProxyFile(true)]);
            if ($container instanceof \Webman\Container) {
                Container::make($className, Container::get($proxyClass));
            } elseif ($container instanceof \DI\Container) {
                $container->set($className, \DI\autowire($proxyClass));
            }
        }
    }
}
