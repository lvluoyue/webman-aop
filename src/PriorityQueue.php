<?php

namespace Luoyue\aop;

use SplPriorityQueue;
use function PHPUnit\Framework\isInt;

class PriorityQueue extends SplPriorityQueue {
    public function compare($priority1, $priority2): int
    {
        if ($priority1 === $priority2) {
            return 0;
        }
        // 数字优先
        if (isInt($priority1) && !isInt($priority2)) {
            return 1;
        }
        if (!isInt($priority1) && isInt($priority2)) {
            return -1;
        }
        return $priority1 <=> $priority2;
    }
}