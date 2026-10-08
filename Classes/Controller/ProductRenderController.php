<?php

declare(strict_types=1);

namespace Pixelant\PxaProductManager\Controller;

use Pixelant\PxaProductManager\Domain\Model\Product;
use Pixelant\PxaProductManager\Domain\Repository\FilterRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

class ProductRenderController extends AbstractController
{
    /**
     * @var FilterRepository
     */
    protected FilterRepository $filterRepository;

    /**
     * @param FilterRepository $filterRepository
     */
    public function injectFilterRepository(FilterRepository $filterRepository): void
    {
        $this->filterRepository = $filterRepository;
    }

    /**
     * Init action, forward to proper action.
     *
     * @param Product|null $product
     */
    public function initAction(Product $product = null): \Psr\Http\Message\ResponseInterface
    {
        return (new \TYPO3\CMS\Extbase\Http\ForwardResponse($product === null ? 'list' : 'show'))
            ->withArguments($product === null ? [] : ['product' => $product]);
    }

    /**
     * App wrapper for lazy loading.
     *
     * @return void
     */
    public function listAction(): \Psr\Http\Message\ResponseInterface
    {
        $filters = [];
        $filterIds = $this->settings['filtering']['filters'] ?? [];

        if ($filterIds) {
            $filters = $this->findRecordsByList((string)($this->settings['filtering']['filters'] ?? ''), $this->filterRepository);
        }

        $this->view->assign('menu', $this->generateMenu());
        $this->view->assign('filters', $filters);
        $this->view->assign('orderBy', json_encode($this->createOrderByArray()));
        $this->view->assign('settingsJson', json_encode($this->lazyListSettings()));
        return $this->htmlResponse();
    }

    /**
     * Show product.
     *
     * @param Product $product
     */
    public function showAction(Product $product): \Psr\Http\Message\ResponseInterface
    {
        $templateLayout = ($product->getProductType()?->getTemplateLayout() ?? '');
        if ($templateLayout !== '') {
            $this->view->setTemplatePathAndFilename($templateLayout);
        }
        $this->view->assignMultiple(compact('product'));
        return $this->htmlResponse();
    }

    /**
     * Prepare lazy loading settings.
     *
     * @param array $categories
     * @return array
     */
    protected function lazyListSettings(): array
    {
        if (!empty($this->settings['pageTreeStartingPoint'])) {
            $pageTreeStartingPoint = $this->settings['pageTreeStartingPoint'];
        } else {
            $pageTreeStartingPoint = $this->getCurrentPageId();
        }

        if (empty($this->settings['productOrderings'])) {
            $this->settings['productOrderings'] = [
                'orderBy' => 'name',
                'orderDirection' => 'asc',
            ];
        }

        if (empty($this->settings['limit'])) {
            $this->settings['limit'] = $this->getListLimit();
        }

        return [
            'storagePid' => $this->storagePid(),
            'pageTreeStartingPoint' => $pageTreeStartingPoint,
            'limit' => $this->getListLimit(),
            'filterConjunction' => $this->settings['filtering']['conjunction'] ?? 'and',
            'hideFilterOptionsNoResult' => (int)($this->settings['filtering']['hideFilterOptionsNoResult'] ?? 0),
        ] + $this->getProductOrderings();
    }

    /**
     * Storages list.
     *
     * @return array
     */
    protected function storagePid(): array
    {
        $frameworkConfiguration = $this->configurationManager->getConfiguration(
            ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK
        );

        return GeneralUtility::intExplode(',', $frameworkConfiguration['persistence']['storagePid'] ?? '');
    }
}
