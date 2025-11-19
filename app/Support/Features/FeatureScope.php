<?php

namespace App\Support\Features;

class FeatureScope
{
    /**
     * @var array<string, bool>
     */
    protected array $features;

    public function __construct(array $features)
    {
        $this->features = $features;
    }

    public function allows(string $feature): bool
    {
        return (bool) ($this->features[$feature] ?? false);
    }
}
