<?php

namespace SprintF\Metadata\Mapping;

/**
 * Общий интерфейс для хранения метаданных свойства класса.
 */
interface PropertyMetadataInterface
{
    /**
     * Имя свойства.
     */
    public function getName(): string;

    /**
     * Добавляет метаданные свойства для конкретной группы.
     */
    public function addDatum(string $group, string $key, mixed $datum): self;

    /**
     * Возвращает метаданные свойства по указанным группам.
     * Если группы не указаны, возвращает только данные для группы '*'.
     *
     * @param array<string> $groups
     *
     * @return iterable<string, mixed>
     */
    public function getDataByGroups(array $groups = []): iterable;

    /**
     * Добавляет метаданные к текущим.
     */
    public function merge(self $propertyMetadata): void;
}
