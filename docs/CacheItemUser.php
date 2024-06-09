<?php

class CacheItemUser extends CacheInvalidationEvent
{
    // В качестве ключа выступает идентификатор пользователя
    public function __construct(protected int $id)
    {
    }
    // Читать значение
    protected function read(): array
    {
        // Выбираем данные из БД
        return sql(
            'SELECT `data` FROM `users` WHERE id = &',
            $this->id
        )->row();
    }
    // Записать значение
    protected function write(array $data): void
    {
        // Обновить данные в БД
        return sql(
            'MODIFY `users` SET = `data` = & WHERE id = &',
            $data,
            $this->id
        )->row();
    }
}
