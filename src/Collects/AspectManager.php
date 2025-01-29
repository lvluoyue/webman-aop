<?php

namespace luoyue\aop\Collects;

class AspectManager
{
    protected static array $container = [];

    public static function get(string $class, string $method): array
    {
        return static::$container[$class][$method] ?? [];
    }

    public static function has(string $class, string $method): bool
    {
        return isset(static::$container[$class][$method]);
    }

    public static function insert(string $class, string $method, \Closure $value): void
    {
        static::$container[$class][$method][] = $value;
    }
}