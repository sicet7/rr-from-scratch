<?php

namespace Sicet7\PropertyInjection\Attributes;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class Inject
{
    /**
     * @var string|null
     */
    private ?string $definition;

    /**
     * @param string|null $definition
     */
    public function __construct(?string $definition = null)
    {
        $this->definition = $definition;
    }

    /**
     * @return string|null
     */
    public function getDefinitionName(): ?string
    {
        return $this->definition;
    }
}