<?php

namespace Shasoft\CacheInvalidation\Tests;

use Shasoft\Dump\Debug;
use Shasoft\CacheInvalidation\CacheInvalidation;

class CacheInvalidationDebug extends CacheInvalidation
{
    private int $label = 0;
    public function __construct()
    {
    }
    // Получить значение ключа
    public function key(string $classname, ...$argsKey): string
    {
        $tmp = explode("\\", $classname);
        return
            array_pop($tmp) . ':' .
            (count($argsKey) == 1 ? $argsKey[0] : serialize($argsKey));
    }
    // Получить значение ключа функции обратного вызова
    public function callableKey(callable $cb, array $argsKey): string
    {
        $refCb = new \ReflectionFunction($cb);
        $ret =  '@' . basename($refCb->getFileName()) . '[' . $refCb->getStartLine() . '-' . $refCb->getEndLine() . ']';
        if (!empty($argsKey)) {
            $ret .= ':' . serialize($argsKey);
        }
        return $ret;
    }
    // Создать метку
    public function createLabel(): mixed
    {
        return ++$this->label;
    }
    // Получить имя ключа массива со значением
    public function keyValue(): string
    {
        return 'value';
    }
    // Получить имя ключа массива с меткой
    public function keyLabel(): string
    {
        return 'label';
    }
    // Получить имя ключа массива с метками
    public function keyLabels(): string
    {
        return 'labels';
    }
    // Получить имя ключа массива с временем жизни
    public function keyTtl(): string
    {
        return 'lifetime';
    }
    // Функция отладки
    public function debug(callable $cb): mixed
    {
        return Debug::log($cb, 1);
    }
};
