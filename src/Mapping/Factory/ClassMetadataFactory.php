<?php

namespace SprintF\Metadata\Mapping\Factory;

use SprintF\Metadata\Mapping\ClassMetadata;
use SprintF\Metadata\Mapping\Loader\LoaderInterface;

/**
 * Фабрика, производящая {@see ClassMetadata}.
 */
class ClassMetadataFactory implements ClassMetadataFactoryInterface
{
    /**
     * @var array<string, ClassMetadata>
     */
    private array $loadedClasses;

    public function __construct(
        protected readonly LoaderInterface $loader,
    ) {
    }

    public function getMetadataFor(string|object $value): ClassMetadata
    {
        $class = $this->getClass($value);

        if (isset($this->loadedClasses[$class])) {
            return $this->loadedClasses[$class];
        }

        $classMetadata = $this->loader->loadClassMetadata($class);

        $reflectionClass = $classMetadata->getReflectionClass();

        // Добавляем метаданные от родительского класса, если он существует
        if (false !== $parent = $reflectionClass->getParentClass()) {
            $classMetadata->merge($this->getMetadataFor($parent->name));
        }

        // Добавляем метаданные от всех интерфейсов
        foreach ($reflectionClass->getInterfaces() as $interface) {
            $classMetadata->merge($this->getMetadataFor($interface->name));
        }

        return $this->loadedClasses[$class] = $classMetadata;
    }

    public function hasMetadataFor(mixed $value): bool
    {
        return \is_object($value) || (\is_string($value) && (\class_exists($value) || \interface_exists($value)));
    }

    private function getClass(object|string $value): string
    {
        if (\is_string($value)) {
            if (!class_exists($value) && !interface_exists($value)) {
                throw new \InvalidArgumentException();
            }

            return ltrim($value, '\\');
        }

        return $value::class;
    }
}
