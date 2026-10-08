<?php

declare(strict_types=1);

namespace Pixelant\PxaProductManager\Controller;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use Pixelant\PxaProductManager\Domain\Repository\CategoryRepository;
use Pixelant\PxaProductManager\Service\NavigationService;

/**
 * Categories controller.
 */
class CategoryController extends AbstractController
{
    /**
     * @var CategoryRepository
     */
    protected CategoryRepository $categoryRepository;

    /**
     * @param CategoryRepository $categoryRepository
     */
    public function injectCategoryRepository(CategoryRepository $categoryRepository): void
    {
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * List navigation.
     */
    public function listAction(): \Psr\Http\Message\ResponseInterface
    {
        $this->view->assign('items', $this->getNavigationService()->getItems());
        return $this->htmlResponse();
    }

    /**
     * Create navigation service.
     *
     * @return NavigationService
     */
    protected function getNavigationService(): NavigationService
    {
        $demand = $this->createCategoriesDemand($this->settings);

        $service = GeneralUtility::makeInstance(
            NavigationService::class,
            $this->categoryRepository->findByUid((int)($this->settings['list']['entryNavigationCategory'] ?? 0)),
            $demand
        );
        if (!empty($this->settings['navigation']['expandAll'])) {
            $service->setExpandAll((bool)$this->settings['navigation']['expandAll']);
        }

        return $service;
    }
}
