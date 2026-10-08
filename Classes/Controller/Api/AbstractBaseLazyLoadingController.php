<?php

declare(strict_types=1);

namespace Pixelant\PxaProductManager\Controller\Api;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\View\JsonView;
use TYPO3\CMS\Extbase\Property\PropertyMappingConfiguration;

abstract class AbstractBaseLazyLoadingController extends ActionController
{
    /**
     * @var string
     */


    /**
     * Initialize configuration.
     */
    public function initializeListAction(): void
    {
        $this->defaultViewObjectName = JsonView::class;
        // Allow to create Demand from arguments
        $allowedProperties = GeneralUtility::trimExplode(
            ',',
            $this->settings['demand']['allowMappingProperties']
                ?? 'offSet,categories,limit,filters,storagePid,orderDirection,orderBy,filterConjunction,hideFilterOptionsNoResult,pageTreeStartingPoint'
        );

        /** @var PropertyMappingConfiguration $demandConfiguration */
        $demandConfiguration = $this->arguments['demand']->getPropertyMappingConfiguration();
        $demandConfiguration->allowProperties(...$allowedProperties);
    }
}
