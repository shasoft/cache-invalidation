<?php

namespace Shasoft\CacheInvalidation\Tests;

use Shasoft\Dump\Debug;
use Shasoft\Dump\DebugInvoke;
use Shasoft\CacheInvalidation\CacheInvalidationLifetime;


class CacheItemFilesPrepare extends CacheInvalidationLifetime
{
    static private int $counter = 0;
    private int $currentCounter;
    static private array $ids = [1, 2, 3];
    public function __construct()
    {
    }
    // Подготовка для чтения значений
    static protected function prepareRead(array $items): void
    {
        DebugInvoke::log(function () use ($items) {
            Debug::log(function () use ($items) {
                foreach ($items as $item) {
                    $item->currentCounter = (++self::$counter);
                }

                foreach (self::$ids as $id) {
                    CacheItemFilePrepare::prepare($id);
                }
            }, 2);
        });
    }
    // Читать значение
    protected function read(): string
    {
        return DebugInvoke::log(function () {
            return Debug::log(function () {
                $ret = [];
                foreach (self::$ids as $id) {
                    $ret[] = CacheItemFilePrepare::get($id);
                }
                return $this->currentCounter . ':' . implode('/', $ret);
            }, 2);
        });
    }
    // Время жизни элемента КЭШа
    protected function ttl(): ?int
    {
        return 1;
    }
}
