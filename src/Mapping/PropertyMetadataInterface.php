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
     * Добавляет свойство к указанной группе метаданных.
     */
    public function addGroup(string $group): self;

    /**
     * Возвращает список всех групп метаданных, в которые входит это свойство.
     *
     * @return array<int, string>
     */
    public function getGroups(): array;

    /**
     * Добавляет метаданные свойства.
     */
    public function addDatum(string $key, mixed $datum): self;

    /**
     * Возвращает все метаданные свойства, включая их ключи и значения.
     *
     * @return iterable<string, mixed>
     */
    public function getData(): iterable;

    /**
     * Добавляет метаданные к текущим.
     */
    public function merge(self $propertyMetadata): void;
}
