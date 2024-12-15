<?php

namespace SprintF\Metadata\Mapping;

/**
 * Общий интерфейс для объектов, хранящих метаданные некоего класса.
 */
interface ClassMetadataInterface
{
    /**
     * Полное имя этого класса.
     */
    public function getName(): string;

    /**
     * Рефлектор этого класса.
     */
    public function getReflectionClass(): \ReflectionClass;

    /**
     * Добавляет метаданные класса.
     */
    public function addDatum(string $key, mixed $datum): self;

    /**
     * Возвращает все метаданные класса, включая их ключи и значения.
     *
     * @return iterable<string, mixed>
     */
    public function getData(): iterable;

    /**
     * Добавляет метаданные свойства класса.
     */
    public function addPropertyMetadata(PropertyMetadataInterface $propertyMetadata): void;

    /**
     * Возвращает список метаданных свойств класса.
     */
    public function getPropertiesMetadata(): array;

    /**
     * Добавляет метаданные к текущим.
     */
    public function merge(self $classMetadata): void;
}
