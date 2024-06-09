<?php

namespace Shasoft\CacheInvalidation;

use Shasoft\CacheInvalidation\CacheInvalidationInterface;

class CacheInvalidation implements CacheInvalidationInterface
{
    // Получить значение ключа
    public function key(string $classname, ...$argsKey): string
    {
        return md5($classname . ':' . serialize($argsKey));
    }
    // Получить значение ключа метки
    public function labelKey(string $key): string
    {
        return '#' . $key;
    }
    // Получить значение ключа функции обратного вызова
    public function callableKey(callable $cb, array $argsKey): string
    {
        $refCb = new \ReflectionFunction($cb);
        return '@' . md5(serialize([$refCb->getFileName(), $refCb->getStartLine(), $refCb->getEndLine(), $argsKey]));
    }
    // Создать метку
    public function createLabel(): mixed
    {
        return microtime(true);
    }
    // Получить имя ключа массива со значением
    public function keyValue(): string
    {
        return 'v';
    }
    // Получить имя ключа массива с меткой
    public function keyLabel(): string
    {
        return '#';
    }
    // Получить имя ключа массива с метками
    public function keyLabels(): string
    {
        return 'l';
    }
    // Получить имя ключа массива с временем жизни
    public function keyTtl(): string
    {
        return 't';
    }
    // Функция отладки
    public function debug(callable $cb): mixed
    {
        return $cb();
    }
};
