<?php

declare(strict_types=1);

namespace Pixelant\PxaProductManager\Service;

use Pixelant\PxaProductManager\Domain\Model\Product;
use Pixelant\PxaProductManager\Domain\Repository\ProductRepository;
use Pixelant\PxaProductManager\Service\Url\UrlBuilderServiceInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Frontend\Typolink\LinkResult;
use TYPO3\CMS\Frontend\Typolink\LinkResultInterface;
use TYPO3\CMS\Frontend\Typolink\TypolinkBuilderInterface;

class TypolinkBuilderService implements TypolinkBuilderInterface
{
    /**
     * Generates link to product single view.
     *
     * @param array $linkDetails
     * @param array $configuration
     * @param ServerRequestInterface $request
     * @param string $linkText
     * @return LinkResultInterface
     */
    public function buildLink(
        array $linkDetails,
        array $configuration,
        ServerRequestInterface $request,
        string $linkText = ''
    ): LinkResultInterface
    {
        $finalUrl = '';

        $record = $this->findRecord($linkDetails);

        if ($record) {
            $urlBuilder = $this->getUrlBuilder();

            if ($record instanceof Product) {
                $finalUrl = $urlBuilder->url($record);
            }
        }

        $result = (new LinkResult('pxappm', $finalUrl))
            ->withLinkConfiguration($configuration)
            ->withLinkText($linkText);
        $target = (string)($configuration['target'] ?? '');
        if ($target !== '') {
            $result = $result->withTarget($target);
        }

        return $result;
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
