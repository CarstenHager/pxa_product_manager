<?php

declare(strict_types=1);

namespace Pixelant\PxaProductManager\Event\Controller;

use Pixelant\PxaProductManager\Domain\Model\DTO\DemandInterface;

/** Ermöglicht projektspezifische Anpassungen nach dem Aufbau einer Produktauswahl. */
final class AfterDemandCreationEvent
{
    public function __construct(private DemandInterface $demand, private array $settings)
    {
    }

    public function getDemand(): DemandInterface
    {
        return $this->demand;
    }

    public function getSettings(): array
    {
        return $this->settings;
    }
}
