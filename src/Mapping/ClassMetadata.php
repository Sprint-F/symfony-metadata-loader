<?php

namespace SprintF\Metadata\Mapping;

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

    public function addPropertyMetadata(PropertyMetadataInterface $propertyMetadata): void
    {
        $this->propertiesMetadata[$propertyMetadata->getName()] = $propertyMetadata;
    }

    public function getPropertiesMetadata(): array
    {
        return $this->propertiesMetadata;
    }

    public function merge(ClassMetadataInterface $classMetadata): void
    {
        foreach ($classMetadata->getData() as $key => $datum) {
            $this->addDatum($key, $datum);
        }

        foreach ($classMetadata->getPropertiesMetadata() as $propertyMetadata) {
            if (isset($this->propertiesMetadata[$propertyMetadata->getName()])) {
                $this->propertiesMetadata[$propertyMetadata->getName()]->merge($propertyMetadata);
            } else {
                $this->addPropertyMetadata($propertyMetadata);
            }
        }
    }
}
