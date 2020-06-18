<?php

declare(strict_types=1);

namespace LenderSpender\LaravelWizard;

use Illuminate\Support\Str;

class StepDetails
{
    private string $stepName;
    private ?string $title;
    private ?string $slug;

    public function __construct(string $stepName, ?string $title = null, ?string $slug = null)
    {
        $this->stepName = $stepName;
        $this->title = $title;
        $this->slug = $slug;
    }

    public function name(): string
    {
        return $this->stepName;
    }

    public function title(): string
    {
        return $this->title ?? $this->name();
    }

    public function slug(): string
    {
        return $this->slug ?? Str::slug($this->name());
    }
}
