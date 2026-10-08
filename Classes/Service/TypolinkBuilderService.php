<?php

declare(strict_types=1);

namespace Pixelant\PxaProductManager\Service;

use Pixelant\PxaProductManager\Configuration\Site\SettingsReader;
use Pixelant\PxaProductManager\Domain\Model\Product;
use Pixelant\PxaProductManager\Domain\Repository\ProductRepository;
use Pixelant\PxaProductManager\Service\Url\UrlBuilderServiceInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\CMS\Frontend\Typolink\AbstractTypolinkBuilder;

class TypolinkBuilderService extends AbstractTypolinkBuilder
{
    /**
     * @var SettingsReader
     */
    protected SettingsReader $siteConfiguration;

    /**
     * @param ContentObjectRenderer $contentObjectRenderer
     * @param TypoScriptFrontendController|null $typoScriptFrontendController
     */
    public function __construct(
        ContentObjectRenderer $contentObjectRenderer,
        TypoScriptFrontendController $typoScriptFrontendController = null
    ) {
        parent::__construct($contentObjectRenderer, $typoScriptFrontendController);

        $this->siteConfiguration = GeneralUtility::makeInstance(SettingsReader::class);
    }

    /**
     * Generates link to product single view.
     *
     * @param array $linkDetails
     * @param string $linkText
     * @param string $target
     * @param array $conf
     * @return array
     */
    public function build(array &$linkDetails, string $linkText, string $target, array $conf): \TYPO3\CMS\Frontend\Typolink\LinkResultInterface
    {
        $finalUrl = '';

        $record = $this->findRecord($linkDetails);

        if ($record) {
            $urlBuilder = $this->getUrlBuilder();

            if ($record instanceof Product) {
                $finalUrl = $urlBuilder->url($record);
            }
        }

        return (new \TYPO3\CMS\Frontend\Typolink\LinkResult('pxappm', $finalUrl))
            ->withLinkText($linkText)->withTarget($target);
    }

    /**
     * Get URL builder.
     *
     * @return UrlBuilderServiceInterface
     */
    protected function getUrlBuilder(): UrlBuilderServiceInterface
    {
        return GeneralUtility::makeInstance(
            UrlBuilderServiceInterface::class
        );
    }

    /**
     * Find record.
     *
     * @param array $linkDetails
     * @return AbstractEntity|null
     */
    protected function findRecord(array $linkDetails): ?AbstractEntity
    {
        $id = (int)($linkDetails['product'] ?? 0);

        if (isset($linkDetails['product'])) {
            $repository = GeneralUtility::makeInstance(ProductRepository::class);

            return $repository->findByUid($id);
        }

        return null;
    }
}
