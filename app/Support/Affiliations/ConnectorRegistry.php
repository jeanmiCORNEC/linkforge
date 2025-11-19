<?php

namespace App\Support\Affiliations;

use Illuminate\Contracts\Container\Container;
use InvalidArgumentException;

class ConnectorRegistry
{
    public function __construct(
        protected Container $container,
        protected array $map,
    ) {
    }

    public static function fromConfig(Container $container): self
    {
        return new self($container, config('affiliate.connectors', []));
    }

    public function resolve(string $platform): AffiliateConnector
    {
        $class = $this->map[$platform] ?? null;

        if (! $class) {
            throw new InvalidArgumentException("Aucun connecteur défini pour {$platform}");
        }

        return $this->container->make($class);
    }
}
