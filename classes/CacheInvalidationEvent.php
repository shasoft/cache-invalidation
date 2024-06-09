<?php

namespace Shasoft\CacheInvalidation;

class CacheInvalidationEvent
{
    // Данные системы работы с КЭШем
    static private ?CacheInvalidationConfig $config = null;
    // Получить элемент по заданным аргументам
    static private function getItem(string $key, array $argsKey): CacheInvalidationItem
    {
        return self::$config->cacheInvalidation->debug(function () use ($key, $argsKey) {
            // Получить элемент КЭШа
            if (array_key_exists($key, self::$config->localCache)) {
                $isHit = true;
                $cacheItem =  self::$config->localCache[$key];
            } else {
                $isHit = false;
                $cacheItem = self::$config->cachePool->getItem($key);
                self::$config->localCache[$key] = $cacheItem;
            }
            // Элемент КЭШ-а существует?
            if ($cacheItem->isHit() || $isHit) {
                // Получить все метки, от которых зависит значение
                $labels = $cacheItem->get()[self::$config->cacheInvalidation->keyLabels()];
                // Проверить метки на изменение
                $labelCacheItems = self::$config->cachePool->getItems(
                    array_keys($labels)
                );
                $hasValueInCache = true;
                foreach ($labelCacheItems as $labelKey => $labelCacheItem) {
                    // Если такого элемента нет ИЛИ 
                    // значение метки не совпадает
                    if (
                        !$labelCacheItem->isHit() ||
                        $labelCacheItem->get() != $labels[$labelKey]
                    ) {
                        // то значит КЭШ не валиден
                        $hasValueInCache = false;
                        // дальше можно не проверять
                        break;
                    }
                }
                // Если значение в КЭШе актуально
                if ($hasValueInCache) {
                    // то вернуть текущий элемент КЭШа
                    return new CacheInvalidationItem(
                        $cacheItem,
                        null
                    );
                }
            }
            // Создать объект для работы с элементом КЭШа
            return new CacheInvalidationItem(
                $cacheItem,
                new static(...$argsKey)
            );
        });
    }
    // Подготовка получения значения
    static public function prepare(...$argsKey): void
    {
        self::$config->cacheInvalidation->debug(function () use ($argsKey) {
            // Переход в режим подготовки
            self::$config->modePrepare(static::class, $argsKey);
        });
    }
    // Получить значение
    static public function get(...$argsKey): mixed
    {
        return self::$config->cacheInvalidation->debug(function () use ($argsKey) {
            // Переход в режим чтения результата
            $item = self::$config->modeGet(static::class, $argsKey);
            // Если нужно
            if (!is_null($item->object)) {
                //-- то читать актуальное значение
                // Сохранить текущие метки
                $labelKeysSave = self::$config->labelKeys;
                // Обнулить
                self::$config->labelKeys = [];
                // Добавить текущий ключ в список родительских ключей
                self::$config->parentKeys[] = $item->cacheItem->getKey();
                // Вызвать метод чтения значения
                $ret = call_user_func([$item->object, 'read']);
                // Удалить текущий ключ из списка родительских ключей
                array_pop(self::$config->parentKeys);
                // Создать новую метку
                $label = self::$config->cacheInvalidation->createLabel($ret);
                // Сохранить в КЭШ значение
                $item->cacheItem->set([
                    self::$config->cacheInvalidation->keyValue() => $ret,
                    self::$config->cacheInvalidation->keyLabel() => $label,
                    self::$config->cacheInvalidation->keyLabels() => self::$config->labelKeys
                ]);
                self::$config->saveLocalCacheItem($item->cacheItem);
                // Вернуть текущие метки
                self::$config->labelKeys = array_merge(
                    $labelKeysSave,
                    self::$config->labelKeys
                );
                // Обнулить объект чтобы он не рассчитывался при следующем вызове
                $item->object = null;
            }
            // Получить данные
            $cacheData = $item->cacheItem->get();
            // Получить ключ метки
            $labelKey = self::$config->cacheInvalidation->labelKey(
                $item->cacheItem->getKey()
            );
            // Есть родитель?
            if (!empty(self::$config->parentKeys)) {
                // Сохранить метку
                $labelCacheItem = self::$config->cachePool->getItem($labelKey);
                $labelCacheItem->set(
                    $cacheData[self::$config->cacheInvalidation->keyLabel()]
                );
                self::$config->cachePool->save($labelCacheItem);
            }
            // Добавить метки
            self::$config->labelKeys = array_merge(
                $cacheData[self::$config->cacheInvalidation->keyLabels()],
                self::$config->labelKeys
            );
            // Добавить текущую метку
            self::$config->labelKeys[$labelKey] =
                $cacheData[self::$config->cacheInvalidation->keyLabel()];
            // Вернуть значение
            return $cacheData[self::$config->cacheInvalidation->keyValue()];
        });
    }
    // Установить значение
    static public function set(...$args): void
    {
        self::$config->cacheInvalidation->debug(function () use ($args) {
            // Рефлексия класса
            $refClass = new \ReflectionClass(static::class);
            // Рефлексия конструктора
            $refConstructor = $refClass->getConstructor();
            // Определить количество параметров ключа
            $sizeKey = $refConstructor ? $refConstructor->getNumberOfParameters() : 0;
            // Определить параметры ключа
            $argsKey = array_slice($args, 0, $sizeKey);
            // Создать объект для работы с элементом КЭШа
            $objectCache = new static(...$argsKey);
            // Вызвать метод изменения значения (если он есть)
            if ($refClass->hasMethod('write')) {
                call_user_func_array(
                    [$objectCache, 'write'],
                    array_slice($args, $sizeKey)
                );
            }
            // Получить ключ
            $key = self::$config->cacheInvalidation->key(static::class, ...$argsKey);
            // Удалить значение в КЭШе
            self::$config->cachePool->deleteItem($key);
            // Удалить значение в ЛОКАЛЬНОМ КЭШе
            if (array_key_exists($key, self::$config->localCache)) {
                unset(self::$config->localCache[$key]);
            }
            // Удалить значение в предЧтении
            if (array_key_exists($key, self::$config->prepareItems)) {
                unset(self::$config->prepareItems[$key]);
            }
            // Удалить из КЭШ-а предЧтения все элементы, которые зависят от текущего
            self::$config->prepareItems = array_filter(
                self::$config->prepareItems,
                function (CacheInvalidationItem $item) use ($key) {
                    return !array_key_exists(
                        self::$config->cacheInvalidation->labelKey($key),
                        $item->cacheItem->get()[self::$config->cacheInvalidation->keyLabels()]
                    );
                }
            );
            // Удалить метку
            $labelKey = self::$config->cacheInvalidation->labelKey($key);
            self::$config->cachePool->deleteItem($labelKey);
        });
    }
};
