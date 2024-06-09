<?php

namespace Shasoft\CacheInvalidation;

interface CacheInvalidationInterface
{
    // Получить значение ключа
    public function key(string $classname, ...$argsKey): string;
    // Получить значение ключа зависимости
    public function labelKey(string $key): string;
    // Создать метку
    public function createLabel(): mixed;
    // Получить имя ключа массива со значением
    public function keyValue(): string;
    // Получить имя ключа массива с меткой
    public function keyLabel(): string;
    // Получить имя ключа массива с метками
    public function keyLabels(): string;
    // Получить имя ключа массива с временем жизни
    public function keyTtl(): string;
    // Функция отладки
    public function debug(callable $cb): mixed;
};
