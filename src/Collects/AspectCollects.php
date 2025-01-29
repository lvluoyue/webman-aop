<?php

namespace luoyue\aop\Collects;

use Generator;
use LinFly\Annotation\Util\AnnotationUtil;
use luoyue\aop\AopBootstrap;
use luoyue\aop\Collects\node\AspectNode;
use ReflectionClass;
use SplFileInfo;
use support\Container;

/**
 * 切面类收集器
 */
class AspectCollects
{

    /** @var AspectNode[] $aspectsClass 切面集合 */
    private array $aspectsClass = [];

    public function addAspects(AspectNode $aspectsClass)
    {
        $this->aspectsClass[] = $aspectsClass;
    }

    /**
     * 扫描切面目录
     * @param string $dir
     * @return void
     */
    public function scan(string $dir): void
    {
        /** @var ProxyCollects $proxyCollects */
        $proxyCollects = Container::get(ProxyCollects::class);
        $dirIterator = new \RecursiveDirectoryIterator($dir);
        $iterator = new \RecursiveIteratorIterator($dirIterator);
        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() == 'php') {
                //扫描文件中的类名集合
                $classNames = AnnotationUtil::getAllClassesInFile($file->getPathname());
                //遍历切入点
                foreach ($this->getAspectsClasses() as $data) {
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
                            $pointcutNode = $proxyCollects->getPointcutNode($className);
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
     * 遍历所有切入点表达式
     * @return Generator
     */
    private function getAspectsClasses(): Generator
    {
        foreach ($this->aspectsClass as $aspectsClass) {
            foreach ($aspectsClass->getPointcut() as $class) {
                yield [
                    'aspect' => $aspectsClass,
                    ...$class,
                ];
            }
        }
    }
}
