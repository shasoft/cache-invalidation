<?php

namespace Shasoft\CacheInvalidation\Tests;

use Shasoft\Dump\Debug;
use Shasoft\Dump\DebugInvoke;


trait CacheItemValues
{
    public static array $values = [];
    static public function clear(): void
    {
        self::$values = [];
    }
    // Записать значение
    protected function write(string $value): void
    {
        DebugInvoke::log(function () use ($value) {
            Debug::log(function () use ($value) {
                self::$values[$this->id] = $value;
            }, 2);
        });
    }
    static public function get(...$argsKey): mixed
    {
        return Debug::log(function () use ($argsKey) {
            return parent::get(...$argsKey);
        });
    }
}
