<?php

namespace Shasoft\CacheInvalidation\Tests;

use Shasoft\Dump\Debug;
use Shasoft\Dump\DebugInvoke;
use Shasoft\CacheInvalidation\CacheInvalidationEvent;
use Shasoft\CacheInvalidation\Tests\CacheItemFilePrepare;


class CacheItemUserPrepare extends CacheInvalidationEvent
{
    use CacheItemValues;
    private string $idPrepare;
    public function __construct(protected int $id)
    {
    }
    // Подготовка для чтения значений
    static protected function prepareRead(array $objects): void
    {
        DebugInvoke::log(function () use ($objects) {
            Debug::log(function () use ($objects) {
                foreach ($objects as $object) {
                    $object->idPrepare = self::$values[$object->id] ?? '?';
                    CacheItemFilePrepare::prepare($object->id * 10);
                }
            }, 2);
        });
    }
    // Читать значение
    protected function read(): string
    {
        return DebugInvoke::log(function () {
            return Debug::log(function () {
                return
                    'UserPrepare(' . $this->id . ',' . $this->idPrepare . CacheItemFilePrepare::get($this->id * 10) . ')';
            }, 2);
        });
    }
}
