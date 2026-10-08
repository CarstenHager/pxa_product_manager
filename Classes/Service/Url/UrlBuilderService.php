<?php

declare(strict_types=1);

namespace Pixelant\PxaProductManager\Service\Url;

use Pixelant\PxaProductManager\Domain\Model\Product;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Class LinkBuilderService.
 */
class UrlBuilderService implements UrlBuilderServiceInterface
{
    /**
     * Link constants.
     */
    public const CATEGORY_ARGUMENT_START_WITH = 'category_';

    public const NAMESPACES = [
        1 => 'tx_pxaproductmanager_productshow',
        9 => 'tx_pxaproductmanager_productrender',
    ];

    public const NAMESPACE_PARAMS = [
        'tx_pxaproductmanager_productshow' => [
            'controller' => 'ProductShow',
            'action' => 'show',
            'pluginName' => 'ProductShow',
            'extensionName' => 'PxaProductManager',
        ],
        'tx_pxaproductmanager_productrender' => [
            'controller' => 'ProductRender',
            'action' => 'init',
        ],
    ];

    /**
     * @var TypoScriptFrontendController
     */
    protected ?TypoScriptFrontendController $tsfe;

    /**
     * Flag if should force absolute url.
     *
     * @var bool
     */
    protected bool $absolute = false;

    /**
     * Initialize.
     *
     * @param TypoScriptFrontendController|null $typoScriptFrontendController
     */
    public function __construct(TypoScriptFrontendController $typoScriptFrontendController = null)
    {
        $this->tsfe = $typoScriptFrontendController ?? $GLOBALS['TSFE'] ?? null;
    }

    /**
     * URL for product.
     *
     * @param Product $product
     * @return string
     */
    public function url(Product $product): string
    {
        $page = $product->getFirstSingleviewPage();
        $pageUid = $page?->getUid() ?? 0;
        $dokType = $page?->getDoktype() ?? 1;
        if ($pageUid === 0) {
            $settings = GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::class)
                ->getConfiguration(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS, 'PxaProductManager');
            $siteSettings = GeneralUtility::makeInstance(\Pixelant\PxaProductManager\Configuration\Site\SettingsReader::class);
            $pageUid = (int)($settings['pids']['singleViewPid'] ?? 0) ?: (int)$siteSettings->getValue('singleViewPid');
            if ($pageUid > 0) {
                $pageRecord = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Domain\Repository\PageRepository::class)->getPage($pageUid);
                $dokType = (int)($pageRecord['doktype'] ?? 1);
            }
        }
        if ($pageUid <= 0) {
            return '';
        }
        $namespace = static::NAMESPACES[$dokType] ?? static::NAMESPACES[1];
        $params = static::NAMESPACE_PARAMS[$namespace];
        $params['product'] = $product->getUid();
        $params = GeneralUtility::implodeArrayForUrl($namespace, $params);

        return $this->buildUri($pageUid, $params);
    }

    /**
     * @param bool $absolute
     */
    public function absolute(bool $absolute): void
    {
        $this->absolute = $absolute;
    }

    /**
     * Generate link.
     *
     * @param int $pageUid
     * @param int $dokType
     * @param string $additionalParams
     * @return string
     */
    protected function buildUri(int $pageUid, string $additionalParams): string
    {
        $typolink = [
            'parameter' => $pageUid,
            'useCacheHash' => true,
            'additionalParams' => $additionalParams,
            'forceAbsoluteUrl' => $this->absolute,
        ];

        /** @var ContentObjectRenderer $contentObjectRenderer */
        $contentObjectRenderer = GeneralUtility::makeInstance(
            ContentObjectRenderer::class,
            $this->tsfe
        );

        $request = $GLOBALS['TYPO3_REQUEST'] ?? null;
        if ($request instanceof \Psr\Http\Message\ServerRequestInterface) {
            $contentObjectRenderer->setRequest($request);
        }

        return $contentObjectRenderer->typoLink_URL($typolink);
    }
}
