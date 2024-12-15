<?php

namespace SprintF\Metadata\Mapping\Factory;

use SprintF\Metadata\Mapping\ClassMetadataInterface;

/**
 * Общий интерфейс для фабрик, производящих {@see ClassMetadataInterface}.
 */
interface ClassMetadataFactoryInterface
{
    public function getMetadataFor(string|object $value): ClassMetadataInterface;

    public function hasMetadataFor(mixed $value): bool;
}
