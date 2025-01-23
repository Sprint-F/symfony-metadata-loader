<?php

namespace SprintF\Metadata\Mapping\Attribute;

/**
 * Атрибут, содержащий в себе метаданные.
 * Требует конкретной реализации в конкретной библиотеке.
 */
#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
abstract class MetadataAttribute
{
    public const DEFAULT_GROUP = '*';

    /**
     * Ключ метаданных. Должен быть обязательно задан в конкретном классе атрибута.
     */
    abstract public function getKey(): string;

    /**
     * Группы к которым должны принадлежать метаданные.
     * По умолчанию возвращаем дефолтную группу, равную по смыслу "Все группы".
     *
     * @return array<int, string>
     */
    public function getGroups(): array
    {
        return [self::DEFAULT_GROUP];
    }
}
