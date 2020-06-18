<?php

declare(strict_types=1);

namespace LenderSpender\LaravelWizard\Exceptions;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class StepNotFoundException extends NotFoundHttpException
{
    public function __construct(string $stepName)
    {
        parent::__construct("No results for step [{$stepName}]");
    }
}
