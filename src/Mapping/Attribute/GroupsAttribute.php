<?php

namespace SprintF\Metadata\Mapping\Attribute;

/**
 * Атрибут, управляющий группами метаданных.
 * Требует конкретной реализации в конкретной библиотеке.
 */
#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD)]
abstract class GroupsAttribute
{
    /**
     * @var array<int, string>
     */
    protected readonly array $groups;

    public function __construct(array|string $groups)
    {
        $this->groups = array_values(array_filter((array) $groups));
    }

    /**
     * @return array<int, string>
     */
    public function getGroups(): array
    {
        return $this->groups;
    }
}
