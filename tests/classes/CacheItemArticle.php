<?php

namespace Shasoft\CacheInvalidation\Tests;

use Shasoft\Dump\Debug;
use Shasoft\Dump\DebugInvoke;
use Shasoft\CacheInvalidation\Tests\CacheItemFile;
use Shasoft\CacheInvalidation\Tests\CacheItemUser;
use Shasoft\CacheInvalidation\CacheInvalidationEvent;


class CacheItemArticle extends CacheInvalidationEvent
{
    use CacheItemValues;
    protected function __construct(protected int $id)
    {
    }
    // Читать значение
    protected function read(): string
    {
        return DebugInvoke::log(function () {
            return Debug::log(function () {
                return
                    'Article(' . $this->id . ',' . (self::$values[$this->id] ?? '?') .
                    CacheItemFile::get($this->id * 10) . '-' . CacheItemUser::get($this->id * 10)
                    . ')';
            }, 2);
        });
    }
}
