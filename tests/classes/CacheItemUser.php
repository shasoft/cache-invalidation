<?php

namespace Shasoft\CacheInvalidation\Tests;

use Shasoft\Dump\Debug;
use Shasoft\Dump\DebugInvoke;
use Shasoft\CacheInvalidation\Tests\CacheItemFile;
use Shasoft\CacheInvalidation\CacheInvalidationEvent;


class CacheItemUser extends CacheInvalidationEvent
{
    use CacheItemValues;
    public function __construct(protected int $id)
    {
    }
    // Читать значение
    protected function read(): string
    {
        return DebugInvoke::log(function () {
            return Debug::log(function () {
                return
                    'User(' . $this->id . ',' . (self::$values[$this->id] ?? '?') . CacheItemFile::get($this->id * 10) . ')';
            }, 2);
        });
    }
}
