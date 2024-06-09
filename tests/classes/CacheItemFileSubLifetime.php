<?php

namespace Shasoft\CacheInvalidation\Tests;

use Shasoft\Dump\Debug;
use Shasoft\Dump\DebugInvoke;
use Shasoft\CacheInvalidation\CacheInvalidationEvent;


class CacheItemFileSubLifetime extends CacheInvalidationEvent
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
                return 'FileSubLifetime(' . $this->id . ',' . (self::$values[$this->id] ?? '?') . '){' . CacheItemFiles::get() . '}';
            }, 2);
        });
    }
}
