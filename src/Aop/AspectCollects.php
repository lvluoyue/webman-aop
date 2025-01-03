<?php

namespace yzh52521\aop\Aop;

use yzh52521\aop\Aop\exception\ParseException;
use yzh52521\aop\AopBootstrap;

/**
 * Class ProxyClasses.
 */
class AspectCollects
{
    /** @var Config */
    private $config;

    private $aspectsClass = [];

    public function __construct(Config $config)
    {
        $this->config      = $config;
    }

    /**
     * getAspectsClass.
     */
    public function getAspectsClass(): array
    {
        return $this->aspectsClass;
    }

    /**
     * @throws \ReflectionException
     */
    public function collectProxy(ProxyCollects $proxyCollects): void
    {
        $array = [];
        foreach ($this->config->getAspectsClasses() as $aspectsClass) {
            $aopClasses = $this->parseAspectClass($aspectsClass, $proxyCollects);
            if (empty($aopClasses)) {
                continue;
            }
            $array[$aspectsClass] = $aopClasses;
        }
        $this->aspectsClass = $array;
    }

    /**
     * @throws \ReflectionException
     */
    private function parseAspectClass(string $class, ProxyCollects $proxyCollects): array
    {
        $aspectReflect   = new \ReflectionClass($class);
        $classesProperty = $aspectReflect->getProperty('classes');
        /** @var array $aopClasses */
        $aopClasses = $classesProperty->getValue(new $class());
        if (empty($aopClasses)) {
            return [];
        }
        $aopCollects = [];
        foreach ($aopClasses as $item) {
            if (!is_string($item)) {
                throw new ParseException(sprintf('class : %s, property: classes : value error, string wanted', $class));
            }
            [$aopClass, $aopMethod] = $this->parseAopClass($item);
            if ($aopClass) {
                $aopCollects[$aopClass] = array_merge($aopCollects[$aopClass] ?? [], [$aopMethod]);
            }
        }

        foreach ($aopCollects as $className => $methods) {
            $proxyCollects->addClassMap($className, $class, $methods, AopBootstrap::getComposerClassLoader()->findFile($className));
        }
        $proxyCollects->setMethodMaps();
        return $aopCollects;
    }

    /**
     * @return array|string[]
     */
    private function parseAopClass(string $class): array
    {
        $infos = explode('::', $class);
        if (1 === count($infos) && class_exists($class)) {
            return [$class, '*'];
        }
        if (2 === count($infos) && class_exists($infos[0]) && !empty($infos[1])) {
            return [$infos[0], $infos[1]];
        }
        return ['', ''];
    }
}
