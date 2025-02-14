# webman-aop

![Packagist Version](https://img.shields.io/packagist/v/luoyue/webman-aop)
![Packagist License](https://img.shields.io/packagist/l/luoyue/webman-aop)
![Packagist Dependency Version](https://img.shields.io/packagist/dependency-v/luoyue/webman-aop/php)
![Packagist Downloads](https://img.shields.io/packagist/dt/luoyue/webman-aop)
![Packagist Stars](https://img.shields.io/packagist/stars/luoyue/webman-aop)

> 注意：本插件依赖container容器，当切入点不在容器中时，aop无法生效

## 简介
**AOP**（Aspect Oriented Programming）是面向切面编程，是一种编程思想，它允许开发者将业务逻辑与业务逻辑以外的代码分离，从而提高代码的可维护性、可扩展性、可测试性。

## AOP核心概念
- **切面（Aspect）**：切面是封装横切关注点的模块。它定义了在何处以及如何应用这些关注点。
- **连接点（Join Point）**：连接点是程序执行过程中可以插入切面的点。例如，方法调用、方法执行、构造函数调用、字段访问等。
- **切入点（Pointcut）**：切入点定义了在哪些连接点上应用切面。它通常使用表达式来匹配特定的连接点。
- **通知（Advice）**：通知是在特定的切入点上执行的代码。通知可以在方法执行之前、之后或异常抛出时执行。常见的通知类型包括：
  - **前置通知（Before）**：在方法执行之前执行。
  - **后置通知（After）**：在方法执行之后执行。
  - **返回通知（After Returning）**：在方法成功返回之后执行。
  - **异常通知（After Throwing）**：在方法抛出异常之后执行。
  - **环绕通知（Around）**：包围方法的执行，可以在方法执行之前和之后自定义行为。
- **织入（Weaving**）：织入是将切面应用到目标对象的过程。织入可以在编译时、类加载时或运行时进行。

## 安装
```
composer require luoyue/webman-aop
```

## 使用方法（php-di环境）
首先创建切入点
```php
<?php
namespace app\service\impl;

class UserServiceImpl
{
    public function info()
    {
        echo 'UserService info' . PHP_EOL;
    }
}
```

创建controller

```php
<?php

namespace app\controller;

use app\service\impl\UserServiceImpl;
use DI\Attribute\Inject;

class Index
{
    #[Inject]
    public UserServiceImpl $userService;

    public function index()
    {
        $this->userService->info();
    }
}
```

然后创建切面类：
```php
<?php

namespace app\aspect;

use app\service\impl\UserServiceImpl;
use luoyue\aop\Attributes\Around;
use luoyue\aop\Attributes\Aspect;
use luoyue\aop\interfaces\ProceedingJoinPointInterface;

#[Aspect] //声明切面类（不加不生效）
class GetUserListAspect
{

    #[Around(UserServiceImpl::class . '::info')] //声明切面方法为环绕通知
    public function around(ProceedingJoinPointInterface $entryClass): mixed
    {
        print_r('around before'.PHP_EOL);
        $res = $entryClass->process();
        print_r('around after'.PHP_EOL);
        return $res;
    }
}
```

php start.php start， 命令行输出结果：
```
around before
UserService info
around after
```


## 在webman容器中使用依赖注入（非phpdi环境）

在 functions.php 定义如下函数:

```php
/**
 *  加载容器的对象
 */
if (! function_exists('load')) {
    function load(string $class)
    {
        return \support\Container::get($class);
    }
}
```

在controller添加如下代码：

```php

    public function index()
    {
        /** @var UserServiceImpl $userService */
        $userService = load(UserServiceImpl::class);
        $userService->info();
    }
```

## 切入顺序
如果有多个切面类对同一个切入点进行切入， 则会按照文件名进行顺序执行。

此外还可以使用`#[Aspect]`注解传入priority参数控制切入顺序，值越小越最先执行。


## 切入点表达式
> **类名表达式**：** (匹配多个\分割的命名空间)，* （匹配一个类名或命名空间）<br>
> **方法名表达式**：* （匹配一个方法名）

示例：
- **匹配一个方法**：`app\service\impl\UserServiceImpl::info`
- **匹配所有方法**：`app\service\impl\UserServiceImpl::*` 或 `app\service\impl\UserServiceImpl`
- **匹配impl目录下的info**：`app\service\impl\*::info`
- **匹配service目录下的所有类**：`app\service\**`
- **匹配所有类的info**：`**::info` （目前有bug）
- **匹配所有类的所有方法**：`**::*` （目前有bug）

## 注解AOP
使用`linfly/annotation`的自定义注解可以实现注解AOP

定义如下注解：
```php
<?php

namespace Luoyue\WebmanMvcCore\annotation\cache;

use LinFly\Annotation\AbstractAnnotationAttribute;

#[\Attribute(\Attribute::TARGET_METHOD)]
class Cached extends AbstractAnnotationAttribute
{

    public function __construct()
    {
        $this->setArguments(func_get_args());
    }

    public static function getParser(): array|string
    {
        return CachedParser::class;
    }

}
```

然后定义如下切面：
```php
<?php

namespace Luoyue\WebmanMvcCore\aop;

use Luoyue\aop\Attributes\AfterReturning;
use Luoyue\aop\interfaces\ProceedingJoinPointInterface;
use Luoyue\WebmanMvcCore\annotation\cache\parser\CachedParser;

class CacheAspect
{
    #[AfterReturning('')]//这里只是标记，实际不会执行（也可以不写）
    public function cachedReturning($res, ProceedingJoinPointInterface $proceedingJoinPoint): mixed
    {
        $sign = $proceedingJoinPoint->getClassName() . '::' . $proceedingJoinPoint->getMethodName();
        $props = call_user_func([CachedParser::class, 'getParams'], $sign);//获取注解参数
        print_r($props);
        return null;
    }
}
```

定义注解处理器：
```php
<?php

namespace Luoyue\WebmanMvcCore\annotation\cache\parser;

use LinFly\Annotation\Contracts\IAnnotationParser;
use Luoyue\aop\Aspect;
use Luoyue\aop\Collects\node\AspectNode;
use Luoyue\aop\enum\AdviceTypeEnum;
use Luoyue\WebmanMvcCore\aop\CacheAspect;

class CachedParser implements iAnnotationParser
{

    public static array $cachedParams = [];

    public static function process(array $item): void
    {
        self::$cachedParams[$item['class'] . '::' . $item['method']] = $item['parameters'];
        $aspectCollects = Aspect::getInstance()->getAspectCollects();
        $proxyCollects = Aspect::getInstance()->getProxyCollects();
        $cachedBefore = $aspectCollects->getAspectNode(CacheAspect::class, 'cachedBefore') ?? new AspectNode(
            CacheAspect::class,//切面类
            'cachedBefore',//切面方法
            AdviceTypeEnum::Before,//通知类型
            []);//获取切面节点
        $proxyCollects->getPointcutNode($item['class'])
            ->addPointcutMethod($item['method'], $cachedBefore);//添加切入点
        Aspect::getInstance()->scan();//重新扫描注解
    }

    public static function getParams(?string $sign = null): array
    {
        if (isset(self::$cachedParams[$sign])) {
            return self::$cachedParams[$sign];
        }
        return self::$cachedParams;
    }
}
```

然后在controller中添加如下代码：

```php
<?php

namespace app\controller;

use LinFly\Annotation\Attributes\Route\GetMapping;
use Luoyue\WebmanMvcCore\annotation\cache\Cached;

class Index
{
    #[GetMapping]
    #[Cached]
    public function index()
    {
        return 'index';
    }
}
```

## TODO
- [x] 新增After、AfterReturning、AfterThrowing、Before注解
- [x] 切入点表达式支持两个通配符（一个星号和两个星号）
- [x] 新增通知排序
- [x] 新增切面排序
- [x] 新增注解切面
- [x] 新增注解切面
- [ ] 优化启动速度