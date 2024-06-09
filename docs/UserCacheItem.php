<?php

namespace Shasoft\CacheInvalidation\Tests;

use Shasoft\CacheInvalidation\CacheInvalidationEvent;

class UserCacheItem extends CacheInvalidationEvent
{
    protected function __construct(protected int $id)
    {
    }
    // Читать значение
    protected function read(): array
    {
        return sqlSelect('SELECT * FROM users WHERE id = &', $this->id)->row();
    }
    // Записать значение
    protected function write(array $data): void
    {
        // добавление/изменение в БД
        // ...
    }
}

// Читать данные 
$data = UserCacheItem::get(777);
// Изменить имя
$data['name'] = 'Shasoft';
// Изменить данные
UserCacheItem::set(777, $data);
