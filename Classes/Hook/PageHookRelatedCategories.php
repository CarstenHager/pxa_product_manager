<?php

declare(strict_types=1);

namespace Pixelant\PxaProductManager\Hook;

use TYPO3\CMS\Backend\Controller\Event\ModifyPageLayoutContentEvent;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\View\ViewFactoryData;
use TYPO3\CMS\Core\View\ViewFactoryInterface;
use TYPO3\CMS\Core\View\ViewInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class PageHookRelatedCategories.
 */
class PageHookRelatedCategories
{
    public function __construct(private readonly ViewFactoryInterface $viewFactory)
    {
    }

    public function __invoke(ModifyPageLayoutContentEvent $event): void
    {
        $pageId = (int)($event->getRequest()->getQueryParams()['id'] ?? 0);
        if ($pageId > 0) {
            $event->addHeaderContent($this->renderForPage($pageId, $event->getRequest()));
        }
    }

    /**
     * @param array $params
     * @param PageLayoutController $pageLayoutController
     * @return string
     */
    public function renderForPage(int $pageId, ?ServerRequestInterface $request = null): string
    {
        $categories = $this->findCategoriesByContentPage($pageId);

        if (empty($categories)) {
            return '';
        }

        $categoriesData = [];
        foreach ($categories as $category) {
            $categoriesData[] = [
                'uri' => $this->editUri((int)$category['uid'], $request),
                'title' => $category['title'],
            ];
        }

        $view = $this->view($request);
        $view->assign('categories', $categoriesData);

        return $view->render();
    }

    /**
     * @return ViewInterface
     */
    protected function view(?ServerRequestInterface $request = null): ViewInterface
    {
        return $this->viewFactory->create(new ViewFactoryData(
            templatePathAndFilename: GeneralUtility::getFileAbsFileName(
                'EXT:pxa_product_manager/Resources/Private/Backend/Templates/PageModule/RelatedCategories.html'
            ),
            request: $request,
        ));
    }

    /**
     * Edit url.
     *
     * @param int $categoryUid
     * @return string
     * @throws \TYPO3\CMS\Backend\Routing\Exception\RouteNotFoundException
     */
    protected function editUri(int $categoryUid, ?ServerRequestInterface $request = null): string
    {
        /** @var UriBuilder $uriBuilder */
        $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);

        return (string)$uriBuilder->buildUriFromRoute(
            'record_edit',
            [
                "edit[sys_category][{$categoryUid}]" => 'edit',
                'returnUrl' => $request !== null ? (string)$request->getUri() : '',
            ],
            UriBuilder::ABSOLUTE_URL
        );
    }

    /**
     * Find categories uids of related page.
     *
     * @param int $page
     * @return array
     */
    protected function findCategoriesByContentPage(int $page): array
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('sys_category');
        $queryBuilder->getRestrictions()->removeAll()->add(
            GeneralUtility::makeInstance(DeletedRestriction::class)
        );

        try {
            return $queryBuilder
                ->select('uid', 'title')
                ->from('sys_category')
                ->where(
                    $queryBuilder->expr()->eq(
                        'pxapm_content_page',
                        $queryBuilder->createNamedParameter(
                            $page,
                            \Doctrine\DBAL\ParameterType::INTEGER
                        )
                    )
                )
                ->executeQuery()
                ->fetchAllAssociative();
        } catch (\Doctrine\DBAL\Exception) {
            // The field is added by the extension's database schema. During an
            // upgrade it may not exist until the Database Analyzer has been
            // run; page rendering must remain available in that interim state.
            return [];
        }
    }
}
