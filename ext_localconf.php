<?php

defined('TYPO3') || die;

(function () {
    // TYPO3 13 no longer loads ext_typoscript_setup.txt automatically.
    // Register the existing setup explicitly so the V12 configuration and
    // all product-manager TypoScript imports remain available.
    $typoScriptSetupFile = \TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName(
        'EXT:pxa_product_manager/ext_typoscript_setup.txt'
    );
    if ($typoScriptSetupFile !== '' && is_file($typoScriptSetupFile)) {
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScriptSetup(
            (string)file_get_contents($typoScriptSetupFile)
        );
    }

    \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\DataHandling\PageDoktypeRegistry::class)->add(
        \Pixelant\PxaProductManager\Domain\Repository\PageRepository::DOKTYPE_PRODUCT_DISPLAY,
        ['type' => 'web', 'allowedTables' => '*']
    );
    // Register Datahandler hook to handle product inheritance.
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']['pxa_product_manager_productInheritance'] = \Pixelant\PxaProductManager\Hook\ProcessDatamap\ProductInheritanceProcessDatamap::class;

    // Register DataHandler hook to validate attribute type.
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']['pxa_product_manager_attributeTypeValidation'] = \Pixelant\PxaProductManager\Hook\ProcessDatamap\AttributeTypeValidationProcessDatamap::class;

    // Register DataHandler hook to clear system cache on attribute save.
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']['pxa_product_manager_attributeSave'] = \Pixelant\PxaProductManager\Hook\ProcessDatamap\AttributeSaveProcessDatamap::class;

    // Configure plugins
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        'PxaProductManager',
        'LazyLoading',
        [
            \Pixelant\PxaProductManager\Controller\Api\LazyLoadingController::class => 'list',
        ],
        [
            \Pixelant\PxaProductManager\Controller\Api\LazyLoadingController::class => 'list',
        ]
    );

    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        'PxaProductManager',
        'LazyAvailableFilters',
        [
            \Pixelant\PxaProductManager\Controller\Api\LazyAvailableFiltersController::class => 'list',
        ],
        [
            \Pixelant\PxaProductManager\Controller\Api\LazyAvailableFiltersController::class => 'list',
        ]
    );

    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        'PxaProductManager',
        'ProductShow',
        [
            \Pixelant\PxaProductManager\Controller\ProductShowController::class => 'show'
        ]
    );

    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        'PxaProductManager',
        'ProductList',
        [
            \Pixelant\PxaProductManager\Controller\LazyProductController::class => 'list'
        ]
    );

    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        'PxaProductManager',
        'ProductRender',
        [
            \Pixelant\PxaProductManager\Controller\ProductRenderController::class => 'init,list,show'
        ]
    );

    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        'PxaProductManager',
        'CustomProductList',
        [
            \Pixelant\PxaProductManager\Controller\CustomProductController::class => 'list'
        ]
    );

    // Register field control for identifier attribute
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1534315213786] = [
        'nodeName' => 'attributeIdentifierControl',
        'priority' => 30,
        'class' => \Pixelant\PxaProductManager\Backend\FormEngine\FieldControl\AttributeIdentifierControl::class
    ];

    $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1608645557] = [
        'nodeName' => 'productParentValue',
        'priority' => '30',
        'class' => \Pixelant\PxaProductManager\Backend\FormEngine\FieldWizard\ParentValueFieldWizard::class,
    ];

    $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1611778775] = [
        'nodeName' => 'hiddenAttributeType',
        'priority' => '30',
        'class' => \Pixelant\PxaProductManager\Backend\FormEngine\FieldWizard\HiddenAttributeTypeValueFieldWizard::class,
    ];

    $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1609921375] = [
        'nodeName' => 'inheritedProductField',
        'priority' => '30',
        'class' => \Pixelant\PxaProductManager\Backend\FormEngine\FieldInformation\InheritedProductFieldInformation::class,
    ];

    // Add attributes fields to Product edit form
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['formDataGroup']['tcaDatabaseRecord'][\Pixelant\PxaProductManager\Backend\FormDataProvider\ProductFormDataProvider::class] = [
        'depends' => [
            \TYPO3\CMS\Backend\Form\FormDataProvider\DatabaseRowInitializeNew::class,
            \TYPO3\CMS\Backend\Form\FormDataProvider\TcaSelectItems::class
        ]
    ];

    // Add attributes fields to Product edit form
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['formDataGroup']['tcaDatabaseRecord'][\Pixelant\PxaProductManager\Backend\FormDataProvider\AttributeValueFormDataProvider::class] = [
        'depends' => [
            \TYPO3\CMS\Backend\Form\FormDataProvider\DatabaseRowInitializeNew::class,
            \TYPO3\CMS\Backend\Form\FormDataProvider\TcaSelectItems::class
        ]
    ];

    $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['formDataGroup']['tcaDatabaseRecord'][\Pixelant\PxaProductManager\Backend\FormDataProvider\NewAttributeRelationRecordsDataProvider::class] = [
        'depends' => [
            \TYPO3\CMS\Backend\Form\FormDataProvider\EvaluateDisplayConditions::class
        ]
    ];

    // Include page TS
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(
        '<INCLUDE_TYPOSCRIPT: source="DIR:EXT:pxa_product_manager/Configuration/TypoScript/PageTS/" extensions="ts">'
    );

    // LinkHandler
    // t3://pxappm?product=[product_id]
    // t3://pxappm?category=[category_id]
    $linkType = 'pxappm';
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['linkHandler'][$linkType]
        = \Pixelant\PxaProductManager\LinkHandler\LinkHandling::class;
    $GLOBALS['TYPO3_CONF_VARS']['FE']['typolinkBuilder'][$linkType]
        = \Pixelant\PxaProductManager\Service\TypolinkBuilderService::class;

    // Register icons
    $icons = [
        'ext-pxa-product-manager-wizard-icon' => 'package.svg',
        'apps-pagetree-productdisplay-default' => 'T3Icons/apps/apps-pagetree-productdisplay-default.svg',
        'apps-pagetree-productdisplay-hideinmenu' => 'T3Icons/apps/apps-pagetree-productdisplay-hideinmenu.svg',
    ];
    $iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
        \TYPO3\CMS\Core\Imaging\IconRegistry::class
    );

    foreach ($icons as $identifier => $path) {
        $iconRegistry->registerIcon(
            $identifier,
            \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
            ['source' => 'EXT:pxa_product_manager/Resources/Public/Icons/Svg/' . $path]
        );
    }

    // Cache framework
    $cacheIdentifier = 'pm_cache_categories';
    if (
        isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'][$cacheIdentifier])
        && !is_array($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'][$cacheIdentifier])
    ) {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'][$cacheIdentifier] = [];
    }
    if (!isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'][$cacheIdentifier]['frontend'])) {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'][$cacheIdentifier]['frontend']
            = \TYPO3\CMS\Core\Cache\Frontend\VariableFrontend::class;
    }
    if (!isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'][$cacheIdentifier]['options'])) {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'][$cacheIdentifier]['options']
            = ['defaultLifetime' => 0];
    }
    if (! isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'][$cacheIdentifier]['groups'])) {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'][$cacheIdentifier]['groups']
            = ['pages', 'system'];
    }

    // Allow backend users to drag and drop the new page type:
    $pdDokType = \Pixelant\PxaProductManager\Domain\Repository\PageRepository::DOKTYPE_PRODUCT_DISPLAY;
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addUserTSConfig(
        'options.pageTree.doktypesToShowInNewPageDragArea := addToList(' . $pdDokType . ')'
    );

    // Add doktype to yoast_seo:s allowedDoktypes.
    if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('yoast_seo')) {
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['yoast_seo']['allowedDoktypes']['product_display'] = $pdDokType;
    }
})();
