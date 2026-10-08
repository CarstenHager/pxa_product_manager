<?php

declare(strict_types=1);

namespace Pixelant\PxaProductManager\UserFunction\Solr;

use Pixelant\PxaProductManager\Domain\Model\Product;
use Pixelant\PxaProductManager\Service\Url\UrlBuilderServiceInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

class Url
{
    /**
     * @var ContentObjectRenderer
     */
    public ContentObjectRenderer $cObj;

    /**
     * @var UrlBuilderServiceInterface
     */
    protected UrlBuilderServiceInterface $urlBuilder;


    /**
     * Init.
     */
    public function __construct()
    {

        $this->urlBuilder = GeneralUtility::makeInstance(UrlBuilderServiceInterface::class);
    }

    /**
     * Generate product url.
     *
     * @param $_
     * @param array $params
     * @return string
     */
    public function generate($_, array $params): string
    {
        $product = $this->mapProduct();
        if ($product === null) {
            return '';
        }

        return $this->urlBuilder->url($product);
    }

    /**
     * Create product model.
     *
     * @return Product|null
     */
    protected function mapProduct(): ?Product
    {
        $dataMapper = GeneralUtility::makeInstance(DataMapper::class);

        return $dataMapper->map(Product::class, [$this->cObj->data])[0] ?? null;
    }
}
