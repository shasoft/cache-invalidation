<?php

namespace Shasoft\CacheInvalidation\Tests;

use Shasoft\Dump\Debug;
use Shasoft\Dump\DebugInvoke;
use Shasoft\CacheInvalidation\CacheInvalidationEvent;


class CacheItemFilePrepare extends CacheInvalidationEvent
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
                }
            }, 2);
        });
    }
    // Читать значение
    protected function read(): string
    {
        return DebugInvoke::log(function () {
            return Debug::log(function () {
                return 'FilePrepare(' . $this->id . ',' . $this->idPrepare . ')';
            }, 2);
        });
    }
}
