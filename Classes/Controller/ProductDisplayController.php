<?php

declare(strict_types=1);

namespace Pixelant\PxaProductManager\Controller;

use Pixelant\PxaProductManager\Domain\Collection\CanCreateCollection;
use Pixelant\PxaProductManager\Domain\Repository\CategoryRepository;
use Pixelant\PxaProductManager\Domain\Repository\FilterRepository;
use Pixelant\PxaProductManager\Service\Category\TreeService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

class ProductDisplayController extends AbstractController
{
    use CanCreateCollection;

    /**
     * @var FilterRepository
     */
    protected FilterRepository $filterRepository;

    /**
     * @var TreeService
     */
    protected TreeService $categoryTree;

    /**
     * @var CategoryRepository
     */
    protected $categoryRepository;

    /**
     * @param TreeService $treeService
     */
    public function injectTreeService(TreeService $treeService): void
    {
        $this->categoryTree = $treeService;
    }

    /**
     * @param CategoryRepository $categoryRepository
     */
    public function injectCategoryRepository(CategoryRepository $categoryRepository): void
    {
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * @param FilterRepository $filterRepository
     */
    public function injectFilterRepository(FilterRepository $filterRepository): void
    {
        $this->filterRepository = $filterRepository;
    }

    /**
     * App wrapper for lazy loading.
     *
     * @return void
     */
    public function listAction(): \Psr\Http\Message\ResponseInterface
    {
        // die($this->settings);
        // Selected filters
        $filters = $this->findRecordsByList((string)($this->settings['filtering']['filters'] ?? ''), $this->filterRepository);
        // Selected categories
        $categories = $this->findRecordsByList(
            (string)($this->settings['lazyList']['entryCategories'] ?? ''),
            $this->categoryRepository
        );

        $this->view->assign('filters', $filters);
        $this->view->assign('settingsJson', json_encode($this->lazyListSettings($categories)));
        return $this->htmlResponse();
    }

    /**
     * Prepare lazy loading settings.
     *
     * @param array $categories
     * @return array
     */
    protected function lazyListSettings(array $categories): array
    {
        return [
            'storagePid' => $this->storagePid(),
            'limit' => $this->getListLimit(),
            'filterConjunction' => $this->settings['filtering']['conjunction'] ?? 'and',
            'hideFilterOptionsNoResult' => (int)($this->settings['filtering']['hideFilterOptionsNoResult'] ?? 0),
            'categories' => $this->categoryTree->childrenIdsRecursiveAndCache($categories),
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
