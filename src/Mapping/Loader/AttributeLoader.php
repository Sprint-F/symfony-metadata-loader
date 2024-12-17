<?php

namespace SprintF\Metadata\Mapping\Loader;

use SprintF\Metadata\Mapping\Attribute\GroupsAttribute;
use SprintF\Metadata\Mapping\Attribute\MetadataAttribute;
use SprintF\Metadata\Mapping\ClassMetadata;
use SprintF\Metadata\Mapping\ClassMetadataInterface;
use SprintF\Metadata\Mapping\PropertyMetadata;

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

        $classGroups = [];

        $propertiesMetadata = $classMetadata->getPropertiesMetadata();

        // Загружаем данные из атрибутов класса
        foreach ($this->loadAttributes($reflectionClass) as $attribute) {
            // ... атрибуты групп, заданные на уровне класса,
            if ($attribute instanceof GroupsAttribute) {
                $classGroups = $attribute->getGroups();
                continue;
            }
            // ... и все остальные атрибуты,
            // из которых мы берем их публичные свойства и передаем из значения в метаданные
            foreach ($attribute as $attributePublicPropertyName => $attributePublicPropertyValue) {
                $classMetadata->addDatum($attribute->getKey().'.'.$attributePublicPropertyName, $attributePublicPropertyValue);
            }
        }

        // Загружаем данные из атрибутов свойств, определенных в классе
        foreach ($reflectionClass->getProperties() as $property) {
            // Вся работа идет только со свойствами, определенными именно в этом классе, у которых есть интересуюшие нас атрибуты.
            if ($property->getDeclaringClass()->name !== $className || 0 === count(iterator_to_array($this->loadAttributes($property)))) {
                continue;
            }

            // Создаем объект $propertyMetadata, если он ранее не был создан
            if (isset($propertiesMetadata[$property->name])) {
                $propertyMetadata = $propertiesMetadata[$property->name];
            } else {
                $propertiesMetadata[$property->name] = $propertyMetadata = new (static::getPropertyMetadataClass())($property->name);
                $classMetadata->addPropertyMetadata($propertyMetadata);
            }

            // Группы, заданные на уровне класса, добавляем к группам атрибутов (свойств).
            foreach ($classGroups as $group) {
                $propertyMetadata->addGroup($group);
            }

            // Затем отрабатываем атрибуты этого свойства (известные этому загрузчику)...
            foreach ($this->loadAttributes($property) as $attribute) {
                // ... атрибуты групп,
                if ($attribute instanceof GroupsAttribute) {
                    foreach ($attribute->getGroups() as $group) {
                        $propertyMetadata->addGroup($group);
                    }
                    continue;
                }
                // ... и все остальные атрибуты,
                // из которых мы берем их данные и передаем из значения в метаданные
                foreach ($this->extractDatumFromAttribute($attribute) as $key => $value) {
                    $propertyMetadata->addDatum($key, $value);
                }
            }
        }

        // Загружаем данные из атрибутов методов, определенных в классе
        foreach ($reflectionClass->getMethods() as $method) {
            // Вся работа идет только с методами, определенными именно в этом классе, у которых есть интересующие нас атрибуты.
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

            // Создаем объект $propertyMetadata, если он ранее не был создан
            $propertyName = lcfirst($matches[2]);
            if (isset($propertiesMetadata[$propertyName])) {
                $propertyMetadata = $propertiesMetadata[$propertyName];
            } else {
                $propertiesMetadata[$propertyName] = $propertyMetadata = new (static::getPropertyMetadataClass())($propertyName);
                $classMetadata->addPropertyMetadata($propertyMetadata);
            }

            // Группы, заданные на уровне класса, добавляем к группам атрибутов (свойств).
            foreach ($classGroups as $group) {
                $propertyMetadata->addGroup($group);
            }

            // Затем отрабатываем атрибуты этого свойства (известные этому загрузчику)...
            foreach ($this->loadAttributes($method) as $attribute) {
                // ... атрибуты групп,
                if ($attribute instanceof GroupsAttribute) {
                    foreach ($attribute->getGroups() as $group) {
                        $propertyMetadata->addGroup($group);
                    }
                    continue;
                }
                // ... и все остальные атрибуты,
                // из которых мы берем их данные и передаем из значения в метаданные
                foreach ($this->extractDatumFromAttribute($attribute) as $key => $value) {
                    $propertyMetadata->addDatum($key, $value);
                }
            }
        }

        return $classMetadata;
    }

    /**
     * Метод, проверяющий, будем ли мы обрабатывать данный атрибут в данном контексте?
     */
    protected function isKnownAttribute(int $target, string $attributeName): bool
    {
        foreach (array_merge(static::getKnownAttributes($target), [GroupsAttribute::class]) as $knownAttribute) {
            if (is_a($attributeName, $knownAttribute, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Генератор интересующих нас атрибутов класса, свойства, метода.
     *
     * @return iterable<int, GroupsAttribute|MetadataAttribute>
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
                yield $attribute->getKey().'.'.$attributePublicPropertyName => $attributePublicPropertyValue;
            }
        }
    }
}
