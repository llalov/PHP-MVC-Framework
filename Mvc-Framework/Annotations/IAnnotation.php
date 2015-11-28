<?php
declare(strict_types=1);

namespace Mvc\Annotations;

interface IAnnotation
{
    function dispatch();
}