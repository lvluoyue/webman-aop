<?php

namespace luoyue\aop\Collects;

use LinFly\Annotation\Util\AnnotationUtil;
use luoyue\aop\AopBootstrap;
use Generator;
use ReflectionClass;
use SplFileInfo;
use support\Container;

/**
 * 切面类收集器
 */
class AspectCollects
{

    /** @var JoinPoint[] $aspectsClass */
    private array $aspectsClass = [];

    public function setAspects(JoinPoint $aspectsClass)
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
                // 根据文件路径获取类名
//                $className = str_replace(['./', '../', '/'], ['', '', '\\'], substr($file->getPathname(), 0, -4));
                $classNames = AnnotationUtil::getAllClassesInFile($file->getPathname());
                foreach ($this->getAspectsClasses() as $data) {
                    foreach ($classNames as $className) {
                        /** @var JoinPoint $aspect */
                        [$aspect, $class, $matchesMethod] = array_values($data);
                        if(preg_match("#^{$class}$#", $className)) {
                            $reflectionClass = new ReflectionClass($className);
                            if($reflectionClass->isInterface() || $reflectionClass->isEnum()) {
                                continue;
                            }
                            $filePath = AopBootstrap::getComposerClassLoader()->findFile($className);
                            $targetData = $proxyCollects->getTargetData($className, $filePath);
                            foreach ($reflectionClass->getMethods() as $method) {
                                if(preg_match("#^{$matchesMethod}$#", $method->getName())) {
                                    $targetData->addTargetClass($method->getName(), $aspect);
                                }
                            }
                            $aspect->addTargetData($targetData);
                        }
                    }
                }
            }
        }
    }

    /**
     * 获取所有class表达式
     * @return Generator
     */
    private function getAspectsClasses(): Generator
    {
        foreach ($this->aspectsClass as $aspectsClass) {
            foreach ($aspectsClass->getAspectClasses() as $class) {
                yield [
                    'aspect' => $aspectsClass,
                    ...$class,
                ];
            }
        }
    }
}
