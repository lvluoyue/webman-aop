<?php

return [
    'enable' => true,
    'proxyPath' => '/runtime/aopCache/proxyClasses',
    // 切入对象的扫描路径
    'scans' => [
        'app',
    ],
    // 切入的切面类
    'aspect' => [
    ],
];
