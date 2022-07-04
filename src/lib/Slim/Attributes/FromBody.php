<?php

namespace Sicet7\Slim\Attributes;

use Sicet7\Slim\Exceptions\ActionDefinitionException;
use Sicet7\Slim\Interfaces\DTOInterface;

#[\Attribute(\Attribute::TARGET_PARAMETER)]
class FromBody
{
    /**
     * @var string|null
     */
    private ?string $dtoFqcn = null;

    /**
     * @param string|null $dtoFqcn
     * @throws ActionDefinitionException
     */
    public function __construct(?string $dtoFqcn = null)
    {
        if ($dtoFqcn !== null && !is_subclass_of($dtoFqcn, DTOInterface::class)) {
            throw new ActionDefinitionException(
                'The "FromBody" Attribute only works on DTO\'s. "' . $dtoFqcn . '" does not implement "' . DTOInterface::class . '".'
            );
        }
        $this->dtoFqcn = $dtoFqcn;
    }

    /**
     * @return string|null
     */
    public function getDtoFqcn(): ?string
    {
        return $this->dtoFqcn;
    }
}