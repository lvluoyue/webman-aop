<?php

namespace Luoyue\aop;

use LinFly\Annotation\Util\AnnotationUtil;
use Luoyue\aop\Collects\AspectCollects;
use Luoyue\aop\Collects\node\AspectNode;
use Luoyue\aop\Collects\ProxyCollects;
use Luoyue\aop\enum\AdviceTypeEnum;
use ReflectionAttribute;
use ReflectionClass;
use SplFileInfo;
use support\Container;

class Aspect
{

    private static Aspect $instance;

    private PriorityQueue $queue;

    private AspectCollects $aspectCollects;

    private array $config;
    private ProxyCollects $proxyCollects;

    private function __construct()
    {
        $this->queue = new PriorityQueue();
        $this->aspectCollects = new AspectCollects();
        $this->proxyCollects = new ProxyCollects();
        $this->config = config('plugin.luoyue.aop.app');
    }

    public static function getInstance(): Aspect
    {
        return self::$instance ??= new self();
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
        $this->proxyCollects->scan();
        return $this;
    }

    /**
     * 执行切面注解扫描逻辑
     * @return void
     */
    private function scanAnnotations(): void
    {
        foreach ($this->queue as $aspectClass) {
            $reflectionClass = new ReflectionClass($aspectClass);
            // 遍历切面类方法
            foreach ($reflectionClass->getMethods() as $method) {
                //过滤非切面注解
                foreach ($this->filterAttributes($method) as $annotation) {
                    //获取方法名
                    $methodName = $method->getName();
                    //获取枚举对象
                    $adviceType = AdviceTypeEnum::tryFrom($annotation->getName());
                    //获取切入点正则表达式数组
                    $matches = array_map([$this, 'getMatchesClasses'], (array)$annotation->getArguments()[0]);
                    //将切面节点添加到切面收集器中
                    $this->aspectCollects->addAspects(new AspectNode($aspectClass, $methodName, $adviceType, $matches));
                }
            }
        }
    }

    /**
     * 扫描切入点目录
     * @param string $dir
     * @return void
     */
    public function scanPointcut(string $dir): void
    {
        $dirIterator = new \RecursiveDirectoryIterator($dir);
        $iterator = new \RecursiveIteratorIterator($dirIterator);
        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() == 'php') {
                //扫描文件中的类名集合
                $classNames = AnnotationUtil::getAllClassesInFile($file->getPathname());
                //遍历切入点
                foreach ($this->aspectCollects->getAspectsClasses() as $data) {
                    //遍历文件类名
                    foreach ($classNames as $className) {
                        /** @var AspectNode $aspect */
                        [$aspect, $class, $matchesMethod] = array_values($data);
                        //匹配切入点
                        if(preg_match("#^{$class}$#", $className)) {
                            $reflectionClass = new ReflectionClass($className);
                            if($reflectionClass->isInterface() || $reflectionClass->isEnum()) {//判断是否是class
                                continue;
                            }
                            $pointcutNode = $this->proxyCollects->getPointcutNode($className);
                            foreach ($reflectionClass->getMethods() as $method) {
                                if(preg_match("#^{$matchesMethod}$#", $method->getName())) {
                                    $pointcutNode->addPointcutMethod($method->getName(), $aspect);
                                }
                            }
                            //将切入节点添加到切面节点
                            $aspect->addMatchesPointcut($pointcutNode);
                        }
                    }
                }
            }
        }
    }

    /**
     * 过滤切面类注解
     * @return ReflectionAttribute[]
     */
    private function filterAttributes(\ReflectionMethod $method): array
    {
        return array_filter($method->getAttributes(), function (ReflectionAttribute $attribute) {
            return in_array($attribute->getName(), AdviceTypeEnum::getAnnotationNames());
        });
    }

    /**
     * 将切入点表达式解析为正则表达式
     * @param string $class
     * @return array
     */
    private function getMatchesClasses(string $class): array
    {
        $explode = explode('::', $class, 2);
        $explode[1] ??= '*';
        return [
            'class' => str_replace(['\\', '**', '*', '#'], ['\\\\', '.#', '[^\\\\]#', '*'], $explode[0]),
            'method' => str_replace(['*', '\\'], ['.*', '\\\\'], $explode[1])
        ];
    }

}