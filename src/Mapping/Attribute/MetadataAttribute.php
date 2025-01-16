<?php

namespace SprintF\Metadata\Mapping\Attribute;

/**
 * Атрибут, содержащий в себе метаданные.
 * Требует конкретной реализации в конкретной библиотеке.
 */
#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD)]
abstract class MetadataAttribute
{
    public const DEFAULT_GROUP = '*';

    /**
     * Ключ метаданных. Должен быть обязательно задан в конкретном классе атрибута.
     */
    abstract public function getKey(): string;

    /**
     * Группы к которым должны принадлежать метаданные.
     *
     * @return  array<int, string>
     */
    abstract public function getGroups(): array;
}
