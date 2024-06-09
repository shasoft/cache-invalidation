<?php

namespace Shasoft\CacheInvalidation;

use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Shasoft\CacheInvalidation\CacheInvalidationInterface;

class CacheInvalidationConfig
{
    // Подготовительный режим по умолчанию выключен
    public bool $prepareEnable = false;
    // Список родительских ключей
    public array $parentKeys = [];
    // Список ключей-меток 
    // (т.е. ключи меток тех элементов, от которых зависит текущее значение)
    public array $labelKeys = [];
    // Локальный КЭШ
    public array $localCache = [];
    // Элементы для подготовки
    public array $prepareItems = [];
    // Список ключей элементов КЭШа "по времени"
    public array $prepareLifetimeKeys = [];
    // Объекты для подготовки
    public array $prepareObjects = [];
    // Объекты для подготовки прочитанные
    public array $prepareObjectsRead = [];
    // Конструктор
    public function __construct(
        public CacheItemPoolInterface $cachePool,
        public CacheInvalidationInterface $cacheInvalidation
    ) {
    }
    // Есть метод подготовки для чтения
    public  function hasPrepareRead(string $classname): bool
    {
        $ret = false;
        // Проверим наличие метода подготовки значений
        while ($classname !== false) {
            if (method_exists($classname, 'prepareRead')) {
                $ret = true;
                break;
            }
            $classname = get_parent_class($classname);
        }

        return $ret;
    }
    // Сохранить значение данных элемента КЭШа
    public function saveLocalCacheItem(CacheItemInterface $cacheItem): void
    {
        $this->localCache[$cacheItem->getKey()] = $cacheItem;
        $this->cachePool->save($cacheItem);
    }
    // Переход в режим подготовки
    public function modePrepare(string $classname, array $argsKey): CacheInvalidationItem
    {
        // Если это не режим подготовки
        if (!$this->prepareEnable) {
            // то значит нужно очистить все данные подготовки
            $this->prepareItems = [];
            $this->prepareObjects = [];
            $this->prepareObjectsRead = [];
            // Установить режим подготовки
            $this->prepareEnable = true;
        }
        //*
        // Есть функция предЧтения?
        if ($this->hasPrepareRead($classname)) {
            // Рефлексия класса
            $refClass = new \ReflectionClass($classname);
            // Рефлексия конструктора
            $refConstructor = $refClass->getConstructor();
            // Определить количество параметров ключа
            $sizeKey = $refConstructor ? $refConstructor->getNumberOfParameters() : 0;
            // Определить параметры ключа
            $argsKey = array_slice($argsKey, 0, $sizeKey);
            // Сгенерировать ключ
            $key = $this->cacheInvalidation->key($classname, ...$argsKey);
            // А может такой элемент уже есть в списке подготовки?
            if (array_key_exists($key, $this->prepareItems)) {
                $item = $this->prepareItems[$key];
            } else {
                // Получить метод
                $refGetItem = $refClass->getMethod('getItem');
                $refGetItem->setAccessible(true);
                // Получить элемент
                $item = $refGetItem->invoke(null, $key, $argsKey);
                //$item = self::getItem($key, $argsKey);
                // Сохранить в КЭШ подготовки
                $this->prepareItems[$key] = $item;
            }
            // Если нужно
            if (
                !is_null($item->object) &&
                !array_key_exists(
                    spl_object_id($item->object),
                    $this->prepareObjectsRead
                )
            ) {
                // то добавить объект в список подготовки
                if (array_key_exists($classname, $this->prepareObjects)) {
                    $this->prepareObjects[$classname][$key] = $item->object;
                } else {
                    $this->prepareObjects[$classname] = [$key => $item->object];
                }
            }
            return $item;
        }
        //*/        
    }
    // Переход в режим получения результата
    public function modeGet(string $classname, array $argsKey): CacheInvalidationItem
    {
        // Если это режим подготовки
        if ($this->prepareEnable) {
            // Прочитать все отсутствующие значения
            while (!empty($this->prepareObjects)) {
                // Берем первый класс элемента
                $_classname = array_keys($this->prepareObjects)[0];
                // Получаем список объектов для расчета
                $objects = array_values($this->prepareObjects[$_classname]);
                unset($this->prepareObjects[$_classname]);
                // Вызвать функцию предварительного чтения для объектов
                $refClass = new \ReflectionClass($_classname);
                $prepareRead = $refClass->getMethod('prepareRead');
                $prepareRead->setAccessible(true);
                $prepareRead->invoke(null, $objects);
                //
                foreach ($objects as $object) {
                    $this->prepareObjectsRead[spl_object_id($object)] = 1;
                }
            }
            // закончить режим подготовки
            $this->prepareEnable = false;
        }
        // Рефлексия класса
        $refClass = new \ReflectionClass($classname);
        // Рефлексия конструктора
        $refConstructor = $refClass->getConstructor();
        // Определить количество параметров ключа
        $sizeKey = $refConstructor ? $refConstructor->getNumberOfParameters() : 0;
        // Определить параметры ключа
        $argsKey = array_slice($argsKey, 0, $sizeKey);
        // Сгенерировать ключ
        $key = $this->cacheInvalidation->key($classname, ...$argsKey);
        // А может такой элемент уже есть в списке подготовки?
        if (array_key_exists($key, $this->prepareItems)) {
            $item = $this->prepareItems[$key];
        } else {
            // Получить элемент
            $refGetItem = $refClass->getMethod('getItem');
            $refGetItem->setAccessible(true);
            $item = $refGetItem->invoke(null, $key, $argsKey);
            // Если нужно читать элемент
            if (!is_null($item->object)) {
                // Если у элемента есть функция предЧтения
                if ($this->hasPrepareRead($classname)) {
                    // то вызвать её
                    $prepareRead = $refClass->getMethod('prepareRead');
                    $prepareRead->setAccessible(true);
                    $prepareRead->invoke(null, [$item->object]);
                }
            }
        }
        return $item;
    }
};
