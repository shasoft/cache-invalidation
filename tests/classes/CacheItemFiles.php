<?php

namespace Shasoft\CacheInvalidation\Tests;

use Shasoft\Dump\Debug;
use Shasoft\Dump\DebugInvoke;
use Shasoft\CacheInvalidation\CacheInvalidationEvent;
use Shasoft\CacheInvalidation\CacheInvalidationLifetime;


class CacheItemFiles extends CacheInvalidationLifetime
{
    static private int $counter = 0;
    static private array $ids = [1, 2, 3];
    public function __construct()
    {
    }
    // Читать значение
    protected function read(): string
    {
        return DebugInvoke::log(function () {
            return Debug::log(function () {
                $ret = [];
                foreach (self::$ids as $id) {
                    $ret[] = CacheItemFile::get($id);
                }
                return (++self::$counter) . ':' . implode('/', $ret);
            }, 2);
        });
    }
    // Время жизни элемента КЭШа
    protected function ttl(): ?int
    {
        return 1;
    }
}
