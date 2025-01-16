<?php

namespace SprintF\Metadata\Mapping\Loader;

use SprintF\Metadata\Mapping\Attribute\MetadataAttribute;
use SprintF\Metadata\Mapping\ClassMetadata;
use SprintF\Metadata\Mapping\ClassMetadataInterface;
use SprintF\Metadata\Mapping\PropertyMetadata;
use SprintF\Metadata\Mapping\PropertyMetadataInterface;

/**
 * Загрузка метаданных через атрибуты PHP.
 */
abstract class AttributeLoader implements LoaderInterface
{
    /**
     * Метод, возвращающий список атрибутов, пригодных для данного загрузчика.
     *
     * @todo: Может это в параметры конструктора?
     *
     * @param \Attribute::TARGET_CLASS|\Attribute::TARGET_PROPERTY|\Attribute::TARGET_METHOD $target Атрибуты класса, свойства, метода?
     */
    abstract protected static function getKnownAttributes(int $target): array;

    /**
     * Метод, возвращающий имя конкретного класса, описывающего метаданные классов.
     *
     * @todo: Может это в параметры конструктора?
     *
     * @return class-string<ClassMetadata>
     */
    abstract protected static function getClassMetadataClass(): string;

    /**
     * Метод, возвращающий имя конкретного класса, описывающего метаданные атрибутов (свойств).
     *
     * @return class-string<PropertyMetadata>
     *
     *@todo: Может это в параметры конструктора?
     */
    abstract protected static function getPropertyMetadataClass(): string;

    public function loadClassMetadata(string $className): ?ClassMetadataInterface
    {
        if (empty($className) || (!class_exists($className) && !interface_exists($className))) {
            return null;
        }

        /** @var ClassMetadataInterface $classMetadata */
        $classMetadata = new (static::getClassMetadataClass())($className);
        $reflectionClass = $classMetadata->getReflectionClass();

        // Загружаем данные из атрибутов класса
        foreach ($this->loadAttributes($reflectionClass) as $attribute) {
            $this->handleAttribute($attribute, $classMetadata);
        }

        // Загружаем данные из атрибутов свойств
        foreach ($reflectionClass->getProperties() as $property) {
            if ($property->getDeclaringClass()->name !== $className || 0 === count(iterator_to_array($this->loadAttributes($property)))) {
                continue;
            }

            $propertyMetadata = $this->getOrCreatePropertyMetadata($classMetadata, $property->name);

            // Обрабатываем атрибуты свойства
            foreach ($this->loadAttributes($property) as $attribute) {
                $this->handleAttribute($attribute, $propertyMetadata);
            }
        }

        // Загружаем данные из атрибутов методов, определенных в классе
        foreach ($reflectionClass->getMethods() as $method) {
            if ($method->getDeclaringClass()->name !== $className || 0 === count(iterator_to_array($this->loadAttributes($method)))) {
                continue;
            }

            // в целях обратной совместимости с компонентом Serializer
            if (0 === stripos($method->name, 'get') && $method->getNumberOfRequiredParameters()) {
                continue;
            }

            // Нас интересуют только методы, начинающиеся с get|is|has|set
            $accessorOrMutator = preg_match('/^(get|is|has|set)(.+)$/i', $method->name, $matches);
            if (!$accessorOrMutator) {
                continue;
            }

            $propertyName = lcfirst($matches[2]);
            $propertyMetadata = $this->getOrCreatePropertyMetadata($classMetadata, $propertyName);

            // Обрабатываем атрибуты метода
            foreach ($this->loadAttributes($method) as $attribute) {
                $this->handleAttribute($attribute, $propertyMetadata);
            }
        }

        return $classMetadata;
    }

    /**
     * Метод реализующий логику работы с каким либо атрибутом.
     *
     * @param MetadataAttribute $attribute
     * @param ClassMetadataInterface|PropertyMetadataInterface $attributeOwnerMetadata
     * @return void
     */
    private function handleAttribute(
        MetadataAttribute $attribute,
        ClassMetadataInterface|PropertyMetadataInterface $attributeOwnerMetadata
    ): void
    {
        $groups = $attribute->getGroups();

        // Если группы не указаны, используем '*'
        if (empty($groups)) {
            $groups = [MetadataAttribute::DEFAULT_GROUP];
        }

        foreach ($attribute as $attributePublicPropertyName => $attributePublicPropertyValue) {
            if ('groups' === $attributePublicPropertyName || null === $attributePublicPropertyValue) {
                continue;
            }

            foreach ($groups as $group) {
                $attributeOwnerMetadata->addDatum(
                    group: $group,
                    key: $attribute->getKey().'.'.$attributePublicPropertyName,
                    datum: $attributePublicPropertyValue
                );
            }
        }
    }

    /**
     * Получает существующие или создает новые метаданные свойства
     */
    private function getOrCreatePropertyMetadata(ClassMetadataInterface $classMetadata, string $propertyName): PropertyMetadataInterface
    {
        $propertiesMetadata = $classMetadata->getPropertiesMetadata();
        
        if (isset($propertiesMetadata[$propertyName])) {
            return $propertiesMetadata[$propertyName];
        }
        
        $propertyMetadata = new (static::getPropertyMetadataClass())($propertyName);
        $classMetadata->addPropertyMetadata($propertyMetadata);
        
        return $propertyMetadata;
    }

    /**
     * Метод, проверяющий, будем ли мы обрабатывать данный атрибут в данном контексте?
     */
    protected function isKnownAttribute(int $target, string $attributeName): bool
    {
        foreach (static::getKnownAttributes($target) as $knownAttribute) {
            if (is_a($attributeName, $knownAttribute, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Генератор интересующих нас атрибутов класса, свойства, метода.
     *
     * @return iterable<int, MetadataAttribute>
     */
    protected function loadAttributes(\ReflectionClass|\ReflectionProperty|\ReflectionMethod $reflector): iterable
    {
        foreach ($reflector->getAttributes() as $attribute) {
            $target = match (true) {
                $reflector instanceof \ReflectionClass => \Attribute::TARGET_CLASS,
                $reflector instanceof \ReflectionProperty => \Attribute::TARGET_PROPERTY,
                $reflector instanceof \ReflectionMethod => \Attribute::TARGET_METHOD,
            };
            if ($this->isKnownAttribute($target, $attribute->getName())) {
                yield $attribute->newInstance();
            }
        }
    }

    /**
     * Метод, получающий от конкретного атрибута интересующие нас данные.
     * В базовой реализации работает с атрибутами, наследующимися от класса MetadataAttribute и их публичными свойствами.
     * В наследниках этого класса может быть расширен.
     */
    protected function extractDatumFromAttribute(object $attribute): iterable
    {
        if ($attribute instanceof MetadataAttribute) {
            foreach ($attribute as $attributePublicPropertyName => $attributePublicPropertyValue) {
                if (null === $attributePublicPropertyValue) {
                    continue;
                }

                yield $attribute->getKey().'.'.$attributePublicPropertyName => $attributePublicPropertyValue;
            }
        }
    }
}
