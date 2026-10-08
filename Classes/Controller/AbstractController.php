<?php

declare(strict_types=1);

namespace Pixelant\PxaProductManager\Controller;

use Pixelant\PxaProductManager\Configuration\Site\SettingsReader;
use Pixelant\PxaProductManager\Domain\Collection\CanCreateCollection;
use Pixelant\PxaProductManager\Domain\Model\DTO\CategoryDemand;
use Pixelant\PxaProductManager\Domain\Model\DTO\DemandInterface;
use Pixelant\PxaProductManager\Domain\Model\DTO\ProductDemand;
use Pixelant\PxaProductManager\Event\Controller\ExcludePagesFromMenuEvent;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\Exception\MissingArrayPathException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3Fluid\Fluid\View\ViewInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

abstract class AbstractController extends ActionController
{
    use CanCreateCollection;

    /**
     * @var SettingsReader
     */
    protected SettingsReader $siteSettings;

    /**
     * @param SettingsReader $settingsReader
     */
    public function injectSettingsReader(SettingsReader $settingsReader): void
    {
        $this->siteSettings = $settingsReader;
    }


    /**
     * Override plugin settings with site settings, before resolving view.
     * @return ViewInterface
     */
    protected function resolveView(): ViewInterface
    {
        $singleViewPid = $this->siteSettings->getValue('singleViewPid');
        if ($singleViewPid) {
            $this->settings['pids']['singleViewPid'] = $singleViewPid;
        }

        return parent::resolveView();
    }


    /**
     * Find records using repository by uids list.
     * Sort records according to given list.
     *
     * @param string $uidsList
     * @param Repository $repository
     * @return array
     */
    protected function findRecordsByList(string $uidsList, Repository $repository): array
    {
        $uids = GeneralUtility::intExplode(',', $uidsList, true);
        if (!empty($uids)) {
            $uids = $repository->overlayUidList($uids);
            $records = $repository->findByUids($uids)->toArray();

            return $this->collection($records)->sortByOrderList($uids, 'uid')->toArray();
        }

        return [];
    }

    /**
     * Create demand object using settings.
     *
     * @param array $settings
     * @param string $className
     * @return DemandInterface
     */
    protected function createDemandFromSettings(array $settings, string $className): DemandInterface
    {
        /** @var DemandInterface $demand */
        $demand = GeneralUtility::makeInstance($className);

        if (!empty($settings['limit'])) {
            $demand->setLimit((int)$settings['limit']);
        }
        if (!empty($settings['offSet'])) {
            $demand->setOffSet((int)$settings['offSet']);
        }
        if (!empty($settings['demand']['orderByAllowed'])) {
            $demand->setOrderByAllowed($settings['demand']['orderByAllowed']);
        }
        if (!empty($settings['orderBy'])) {
            $demand->setOrderBy($settings['orderBy']);
        }
        if (!empty($settings['orderDirection'])) {
            $demand->setOrderDirection($settings['orderDirection']);
        }

        $this->eventDispatcher->dispatch(new \Pixelant\PxaProductManager\Event\Controller\AfterDemandCreationEvent($demand, $settings));

        return $demand;
    }

    /**
     * Create categories demand.
     *
     * @param array $settings
     * @param string $className
     * @return DemandInterface
     */
    protected function createCategoriesDemand(
        array $settings,
        string $className = CategoryDemand::class
    ): DemandInterface {
        $settings = array_merge($settings, (array)($this->settings['categoriesOrderings'] ?? []));
        $className = $this->readFromSettings('demand.objects.categoryDemand', $className);

        $demand = $this->createDemandFromSettings($settings, $className);
        if (!empty($settings['navigation']['hideCategoriesWithoutProducts'])) {
            $demand->setHideCategoriesWithoutProducts((bool)$settings['navigation']['hideCategoriesWithoutProducts']);
        }
        if (!empty($settings['onlyVisibleInNavigation'])) {
            $demand->setOnlyVisibleInNavigation((bool)$settings['onlyVisibleInNavigation']);
        }
        if (!empty($settings['parent'])) {
            $demand->setParent($settings['parent']);
        }

        return $demand;
    }

    /**
     * Create products demand.
     *
     * @param array $settings
     * @param string $className
     * @return DemandInterface
     */
    protected function createProductsDemand(
        array $settings,
        string $className = ProductDemand::class
    ): DemandInterface {
        $settings = array_merge($settings, (array)($this->settings['productOrderings'] ?? []));
        $className = $this->readFromSettings('demand.objects.productDemand', $className);

        $demand = $this->createDemandFromSettings($settings, $className);
        if (!empty($settings['categories'])) {
            $demand->setCategories($settings['categories']);
        }
        if (!empty($settings['categoryConjunction'])) {
            $demand->setCategoryConjunction($settings['categoryConjunction']);
        }
        $demand->setPageTreeStartingPoint(
            (int)($settings['pageTreeStartingPoint'] ?? $this->getCurrentPageId())
        );

        return $demand;
    }

    /**
     * Create order by options from settings.
     *
     * @return void
     */
    protected function createOrderByArray()
    {
        $orderBy = [];
        $listOrderBy = $this->settings['listView']['orderBy'] ?? [];
        if (!is_array($listOrderBy)) {
            return [];
        }

        foreach ($listOrderBy as $listOrderByItem) {
            if (!is_array($listOrderByItem) || !is_string($listOrderByItem['value'] ?? null)) {
                continue;
            }
            $key = (string)($listOrderByItem['key'] ?? '');
            $text = $key !== '' ? LocalizationUtility::translate($key, 'PxaProductManager') : null;
            $text ??= $listOrderByItem['value'];
            $orderBy[] = [
                'value' => $listOrderByItem['value'],
                'text' => $text,
            ];
        }

        return $orderBy;
    }

    /** Default pagination also applies to content elements without a FlexForm. */
    protected function getListLimit(): int
    {
        return max(0, (int)($this->settings['limit'] ?? $this->settings['listView']['limit'] ?? 12));
    }

    protected function getProductOrderings(): array
    {
        $orderings = $this->settings['productOrderings'] ?? [];
        return (is_array($orderings) ? $orderings : []) + ['orderBy' => 'name', 'orderDirection' => 'asc'];
    }

    /**
     * Read from settings by path or return default value.
     *
     * @param string $path
     * @param mixed $default
     * @return mixed
     */
    protected function readFromSettings(string $path, $default = null)
    {
        try {
            $value = ArrayUtility::getValueByPath($this->settings, $path, '.');
        } catch (MissingArrayPathException $exception) {
            $value = null;
        }

        return $value ?: $default;
    }

    /**
     * Return the current frontend page ID.
     *
     * TYPO3 13 provides page information through the PSR-7 request. The
     * fallback keeps this controller usable on TYPO3 12 during the transition.
     */
    protected function getCurrentPageId(): int
    {
        $request = isset($this->request) ? $this->request : null;
        $pageInformation = $request?->getAttribute('frontend.page.information');
        if (is_object($pageInformation) && method_exists($pageInformation, 'getId')) {
            return (int)$pageInformation->getId();
        }

        return (int)($GLOBALS['TSFE']->id ?? 0);
    }

    /**
     * Return the current frontend root line.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function getCurrentRootLine(): array
    {
        $request = isset($this->request) ? $this->request : null;
        $pageInformation = $request?->getAttribute('frontend.page.information');
        if (is_object($pageInformation) && method_exists($pageInformation, 'getRootLine')) {
            return (array)$pageInformation->getRootLine();
        }

        return (array)($GLOBALS['TSFE']->rootLine ?? []);
    }

    /**
     * Get number of menu levels.
     *
     * @return int
     */
    protected function getMenuLevels(): int
    {
        return (int)($this->settings['menuLevels'] ?? 0);
    }

    /**
     * Get number of menu levels for list view.
     *
     * @return int
     */
    protected function getListViewMenuLevels(): int
    {
        return (int)($this->settings['listView']['menuLevels'] ?? 0);
    }

    /**
     * Return the content object from the PSR-7 request.
     *
     * ConfigurationManager->getContentObject() was deprecated in TYPO3 12
     * and removed in TYPO3 13. The current content object is provided as a
     * request attribute by the frontend rendering pipeline.
     */
    protected function getCurrentContentObject(): ContentObjectRenderer
    {
        $contentObject = $this->request->getAttribute('currentContentObject');
        if (!$contentObject instanceof ContentObjectRenderer) {
            throw new \RuntimeException(
                'The current content object is not available in the frontend request.',
                1728371301
            );
        }

        return $contentObject;
    }

    /**
     * Generate menu for fluid variable based on flexform settings.
     *
     * @return array
     */
    protected function generateMenu(): array
    {
        $levels = $this->getMenuLevels();
        $menuLevels = $this->getListViewMenuLevels();
        if ($menuLevels > 0 && $menuLevels < 4) {
            $levels = $menuLevels;
        }

        $data = [];

        if ((int)$levels > 0) {
            $currentPageId = $this->getCurrentPageId();
            $rootLine = $this->getCurrentRootLine();

            $fixedMenuPageId = (int)($this->settings['listView']['menuPageId'] ?? 0);
            if ($fixedMenuPageId > 0) {
                $currentPageId = $fixedMenuPageId;
            }

            $subpages = $this->getMenuOfSubpages($currentPageId, $levels);
            // Check if we are on "last level" step one page up in menu.
            if (empty($subpages)) {
                $parentPageId = $rootLine[count($rootLine) - 2]['uid'] ?? false;
                if (!empty($parentPageId)) {
                    $currentPageId = (int)$parentPageId;
                    $subpages = $this->getMenuOfSubpages($currentPageId, $levels);
                }
            }

            $current = $this->getMenuOfCurrentPage($currentPageId);

            $data = $current[0] ?? [];
            $data['children'] = $subpages ?? [];
        }

        return $data;
    }

    /**
     * Generate menu for fluid variable based on flexform settings.
     *
     * @param int $pageId
     * @param int $levels
     * @return array
     */
    protected function getMenuOfSubpages(int $pageId, int $levels): array
    {
        $menuDirectoryProcessor = GeneralUtility::makeInstance(
            \TYPO3\CMS\Frontend\DataProcessing\MenuProcessor::class
        );

        $excludeUidList = $this->settings['listView']['excludeUidList'] ?? '';

        $excludePagesFromMenuEvent = GeneralUtility::makeInstance(ExcludePagesFromMenuEvent::class, $excludeUidList);
        $event = $this->eventDispatcher->dispatch($excludePagesFromMenuEvent);
        $excludeUidList = $event->getExcludeUidList();

        return $menuDirectoryProcessor->process(
            $this->getCurrentContentObject(),
            [],
            [
                'special' => 'directory',
                'special.' => ['value' => $pageId],
                'levels' => $levels,
                'as' => 'subpages',
                'excludeUidList' => $excludeUidList,
            ],
            []
        )['subpages'] ?? [];
    }

    /**
     * Generate menu for fluid variable based on flexform settings.
     *
     * @param int $pageId
     * @return array
     */
    protected function getMenuOfCurrentPage(int $pageId): array
    {
        $menuListProcessor = GeneralUtility::makeInstance(
            \TYPO3\CMS\Frontend\DataProcessing\MenuProcessor::class
        );

        return $menuListProcessor->process(
            $this->getCurrentContentObject(),
            [],
            [
                'special' => 'list',
                'special.' => ['value' => $pageId],
                'as' => 'current',
            ],
            []
        )['current'] ?? [];
    }
}
