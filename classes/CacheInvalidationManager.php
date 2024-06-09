<?php

namespace Shasoft\CacheInvalidation;

use Psr\Cache\CacheItemPoolInterface;
use Shasoft\CacheInvalidation\CacheInvalidationLifetime;

class CacheInvalidationManager
{
    // Изменить приватное свойство класса
    static private function setProperty(\ReflectionClass $refClass, string $name, mixed $value)
    {
        // Установить ссылку на КЭШ
        $refProperty = $refClass->getProperty($name);
        $refProperty->setAccessible(true);
        $refProperty->setValue($value);
    }
    // Установить параметры
    static private function setConfigForClass(
        string $classname,
        CacheInvalidationConfig $config
    ): void {
        //
        $refClass = new \ReflectionClass($classname);
        // Установить ссылку на данные
        self::setProperty($refClass, 'config', $config);
    }
    // Установить параметры
    static public function setConfig(
        CacheItemPoolInterface $cachePool,
        ?CacheInvalidationInterface $cacheInvalidation = null
    ): void {
        // Если не указано
        if (is_null($cacheInvalidation)) {
            // то берем значение по умолчанию
            $cacheInvalidation = new CacheInvalidation;
        }
        // Настройки
        $config = new CacheInvalidationConfig(
            $cachePool,
            $cacheInvalidation
        );
        // Установить параметры для класса элемента КЭШа запоминания до изменения
        self::setConfigForClass(
            CacheInvalidationEvent::class,
            $config
        );
        // Установить параметры для класса элемента КЭШа запоминания на время
        self::setConfigForClass(
            CacheInvalidationLifetime::class,
            $config
        );
    }
};
