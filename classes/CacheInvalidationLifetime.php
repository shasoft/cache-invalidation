<?php

namespace Shasoft\CacheInvalidation;

class CacheInvalidationLifetime
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
                $lifetime = $cacheItem->get()[self::$config->cacheInvalidation->keyTtl()];
                // Если время не вышло
                if (is_null($lifetime) || time() < $lifetime) {
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
            $item = self::$config->modePrepare(static::class, $argsKey);
            // Список ключей элементов КЭШа "по времени"
            self::$config->prepareLifetimeKeys[] = $item->cacheItem->getKey();
        });
    }
    // Получить значение
    static public function get(...$argsKey): mixed
    {
        return self::$config->cacheInvalidation->debug(function () use ($argsKey) {
            // Проверить все элементы КЭШа
            if (!empty(self::$config->prepareLifetimeKeys)) {
                // Текущее время
                $now = time();
                // Удалить все элементы, время которых истекло
                self::$config->prepareLifetimeKeys =
                    array_filter(
                        self::$config->prepareLifetimeKeys,
                        function (string $key) use ($now) {
                            $cacheData = self::$config->prepareItems[$key]->cacheItem->get();
                            if (is_array($cacheData)) {

                                $ttl = $cacheData[self::$config->cacheInvalidation->keyTtl()];
                                if (is_null($ttl) || $ttl < $now) {
                                    return true;
                                }
                                // Удалить элемент из списка элементов предЧтения
                                if (array_key_exists(
                                    $key,
                                    self::$config->prepareItems
                                )) {
                                    unset(self::$config->prepareItems[$key]);
                                }
                                // Удалить из списка ключей
                                return false;
                            }
                            return true;
                        }
                    );
            }
            // Переход в режим чтения результата
            $item = self::$config->modeGet(static::class, $argsKey);
            // Если нужно
            if (!is_null($item->object)) {
                //-- то читать актуальное значение
                /*
                // Сохранить текущие метки
                $labelKeysSave = self::$labelKeys;
                // Обнулить
                self::$labelKeys = [];
                //*/
                /*
                // Добавить текущий ключ в список родительских ключей
                self::$parentKeys[] = $item->cacheItem->getKey();
                //*/
                // Вызвать метод чтения значения
                $ret = call_user_func([$item->object, 'read']);
                /*
                // Удалить текущий ключ из списка родительских ключей
                array_pop(self::$parentKeys);
                //*/
                /*
                //*/
                // Создать новую метку
                $label = self::$config->cacheInvalidation->createLabel($ret);
                // Сохранить в КЭШ значение
                $item->cacheItem->set([
                    self::$config->cacheInvalidation->keyValue() => $ret,
                    self::$config->cacheInvalidation->keyLabel() => $label,
                    self::$config->cacheInvalidation->keyTtl() => (time() + $item->object->ttl())
                    /*                    
                    self::$config->cacheInvalidation->keyLabels() => self::$labelKeys
                    //*/
                ]);
                self::$config->saveLocalCacheItem($item->cacheItem);
                /*
                // Вернуть текущие метки
                self::$labelKeys = array_merge($labelKeysSave, self::$labelKeys);
                //*/
                // Обнулить объект чтобы он не рассчитывался при следующем вызове
                $item->object = null;
            }
            // Получить данные
            $cacheData = $item->cacheItem->get();
            // Получить ключ метки
            $labelKey = self::$config->cacheInvalidation->labelKey(
                $item->cacheItem->getKey()
            );
            // Добавить текущий ключ в список зависимых меток 
            // для элементов зависимых от событий
            self::$config->labelKeys[$labelKey] = $cacheData[self::$config->cacheInvalidation->keyLabel()];
            /*
            // Есть родитель?
            if (!empty(self::$parentKeys)) {
            //*/
            // Сохранить метку
            $labelCacheItem = self::$config->cachePool->getItem($labelKey);
            $labelCacheItem->set(
                $cacheData[self::$config->cacheInvalidation->keyLabel()]
            );
            $labelCacheItem->expiresAt(
                (new \DateTime())->setTimestamp($cacheData[self::$config->cacheInvalidation->keyTtl()])
            );
            self::$config->cachePool->save($labelCacheItem);
            /*
            }
            //*/
            /*
            // Добавить метки
            self::$labelKeys = array_merge(
                $config[self::$config->cacheInvalidation->keyLabels()],
                self::$labelKeys
            );
            // Добавить текущую метку
            self::$labelKeys[$labelKey] =
                $config[self::$config->cacheInvalidation->keyLabel()];
            //*/
            // Вернуть значение
            return $cacheData[self::$config->cacheInvalidation->keyValue()];
        });
    }
    // Время жизни элемента КЭШа
    protected function ttl(): ?int
    {
        return null;
    }
};
