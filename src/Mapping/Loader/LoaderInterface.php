<?php

namespace SprintF\Metadata\Mapping\Loader;

use SprintF\Metadata\Mapping\ClassMetadataInterface;

/**
 * Общий интерфейс для способов загрузки метаданных.
 */
interface LoaderInterface
{
    public function loadClassMetadata(string $className): ?ClassMetadataInterface;
}
