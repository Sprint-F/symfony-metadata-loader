<?php

namespace SprintF\Metadata\Mapping;

use SprintF\Metadata\Mapping\Attribute\MetadataAttribute;

abstract class ClassMetadata implements ClassMetadataInterface
{
    protected ?\ReflectionClass $reflectionClass = null;

    protected array $data = [];

    protected array $propertiesMetadata = [];

    public function __construct(protected readonly string $name)
    {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getReflectionClass(): \ReflectionClass
    {
        if (!$this->reflectionClass) {
            $this->reflectionClass = new \ReflectionClass($this->getName());
        }

        return $this->reflectionClass;
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

    public function addPropertyMetadata(PropertyMetadataInterface $propertyMetadata): void
    {
        $this->propertiesMetadata[$propertyMetadata->getName()] = $propertyMetadata;
    }

    public function getPropertiesMetadata(): array
    {
        return $this->propertiesMetadata;
    }

    public function getPropertiesMetadataByGroups(array $groups = []): array
    {
        // Если группы не указаны или пустой массив, ищем только в '*'
        if (empty($groups)) {
            $groups = [MetadataAttribute::DEFAULT_GROUP];
        }

        $result = [];

        foreach ($this->propertiesMetadata as $name => $metadata) {
            $propertyData = $metadata->getDataByGroups($groups);
            if (!empty($propertyData)) {
                $result[$name] = $metadata;
            }
        }

        return $result;
    }

    public function merge(ClassMetadataInterface $classMetadata): void
    {
        // Объединяем данные по группам
        foreach ($classMetadata->getData() as $group => $groupData) {
            foreach ($groupData as $key => $datum) {
                $this->addDatum($group, $key, $datum);
            }
        }

        // Объединяем метаданные свойств
        foreach ($classMetadata->getPropertiesMetadata() as $propertyMetadata) {
            if (isset($this->propertiesMetadata[$propertyMetadata->getName()])) {
                $this->propertiesMetadata[$propertyMetadata->getName()]->merge($propertyMetadata);
            } else {
                $this->addPropertyMetadata($propertyMetadata);
            }
        }
    }
}
