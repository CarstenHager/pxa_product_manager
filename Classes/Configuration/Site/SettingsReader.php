<?php

declare(strict_types=1);

namespace Pixelant\PxaProductManager\Configuration\Site;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Site\Entity\Site;

/** Liest Produktmanager-Einstellungen aus der Site des aktuellen Requests. */
class SettingsReader
{
    protected string $prefix = 'pxapm_';
    protected ?ServerRequestInterface $request = null;

    public function setRequest(ServerRequestInterface $request): void
    {
        $this->request = $request;
    }

    public function getValue(string $key)
    {
        $request = $this->request ?? ($GLOBALS['TYPO3_REQUEST'] ?? null);
        $site = $request instanceof ServerRequestInterface ? $request->getAttribute('site') : null;
        if (!$site instanceof Site) {
            return null;
        }
        $key = str_starts_with($key, $this->prefix) ? $key : $this->prefix . $key;
        return $site->getConfiguration()[$key] ?? null;
    }
}
