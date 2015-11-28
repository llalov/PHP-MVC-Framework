<?php

declare(strict_types=1);

namespace Mvc\Annotations;

abstract class Annotation implements IAnnotation
{
    /**
     * Annotation constructor.
     */
    protected function __construct()
    {
    }

    public abstract function dispatch();
}