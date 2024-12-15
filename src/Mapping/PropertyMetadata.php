<?php

namespace SprintF\Metadata\Mapping;

/**
 * Абстрактный класс для хранения метаданных свойств классов.
 * Требует расширения в конкретной библиотеке.
 */
abstract class PropertyMetadata implements PropertyMetadataInterface
{
    protected array $groups = [];

    protected array $data = [];

    public function __construct(protected readonly string $name)
    {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function addGroup(string $group): self
    {
        if (!\in_array($group, $this->groups)) {
            $this->groups[] = $group;
        }

        return $this;
    }

    public function getGroups(): array
    {
        return $this->groups;
    }

    public function addDatum(string $key, mixed $datum): self
    {
        if (!isset($this->data[$key])) {
            $this->data[$key] = $datum;
        }

        return $this;
    }

    public function getData(): iterable
    {
        return $this->data;
    }

    public function merge(PropertyMetadataInterface $propertyMetadata): void
    {
        foreach ($propertyMetadata->getGroups() as $group) {
            $this->addGroup($group);
        }
        foreach ($propertyMetadata->getData() as $key => $value) {
            $this->addDatum($key, $value);
        }
    }
}
