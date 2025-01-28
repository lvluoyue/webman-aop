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
```
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

```
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

```
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

```

    public function index()
    {
        /** @var UserServiceImpl $userService */
        $userService = load(UserServiceImpl::class);
        $userService->info();
    }
```

## 切入顺序
如果有多个切面类对同一个类方法进行切入， 默认会按照文件名字母升序执行

暂不支持自定义切入顺序

## 切入点表达式
目前支持的表达式有：
- **匹配一个方法**：`类名::方法名`
- **匹配所有方法**：`类名::*` 或 `类名`
- **匹配service类的info**：`app\service\*::info`
- **匹配service类的所有方法**：`app\service\*::*`
- **匹配所有类的info**：`*::info` （目前有bug）
- **匹配所有类的所有方法**：`*::*` （目前有bug）
