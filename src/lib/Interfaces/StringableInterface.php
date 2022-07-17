<?php

namespace Sicet7\Interfaces;

interface StringableInterface extends \Stringable
{
    /**
     * @return string
     */
    public function toString(): string;
}