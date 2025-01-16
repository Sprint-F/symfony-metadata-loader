<?php

namespace SprintF\Metadata\Mapping;

use SprintF\Metadata\Mapping\Attribute\MetadataAttribute;

/**
 * Абстрактный класс для хранения метаданных свойств классов.
 * Требует расширения в конкретной библиотеке.
 */
abstract class PropertyMetadata implements PropertyMetadataInterface
{
    protected array $groups = [];

    /**
     * @var array<string, array<string, mixed>> Формат: ['group' => ['key' => value]]
     */
    protected array $data = [];

    public function __construct(protected readonly string $name)
    {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function addDatum(string $group, string $key, mixed $datum): self
    {
        $this->data[$group][$key] = $datum;

        return $this;
    }

    public function getData(): iterable
    {
        return $this->data;
    }

    public function getDataByGroups(array $groups = []): iterable
    {
        // Если группы не указаны или пустой массив, используем только '*'
        if (empty($groups)) {
            return $this->data[MetadataAttribute::DEFAULT_GROUP] ?? [];
        }

        $result = [];

        // Затем добавляем/перезаписываем данные для конкретных групп
        foreach ($groups as $group) {
            if (isset($this->data[$group])) {
                $result = array_merge($result, $this->data[$group]);
            }
        }

        return $result;
    }

    public function merge(PropertyMetadataInterface $propertyMetadata): void
    {
        foreach ($propertyMetadata->getData() as $group => $groupData) {
            foreach ($groupData as $key => $value) {
                $this->addDatum($group, $key, $value);
            }
        }
    }
}
