<?php

namespace Sicet7\RoadRunner\Events;

final class BadRequest
{
    /**
     * @var \Throwable
     */
    private \Throwable $throwable;

    public function __construct(\Throwable $throwable)
    {
        $this->throwable = $throwable;
    }

    /**
     * @return \Throwable
     */
    public function getThrowable(): \Throwable
    {
        return $this->throwable;
    }
}