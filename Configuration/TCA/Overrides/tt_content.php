<?php

defined('TYPO3') || die;

(function (): void {
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
        'pxa_product_manager',
        'ProductList',
        'Product List',
        'EXT:pxa_product_manager/Resources/Public/Icons/Extension.svg',
        'Product Manager',
        '',
        'FILE:EXT:pxa_product_manager/Configuration/FlexForms/flexform_product_list.xml'
    );

    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
        'pxa_product_manager',
        'ProductShow',
        'Product Show',
        'EXT:pxa_product_manager/Resources/Public/Icons/Extension.svg',
        'Product Manager',
        '',
        'FILE:EXT:pxa_product_manager/Configuration/FlexForms/flexform_product_show.xml'
    );

    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
        'pxa_product_manager',
        'ProductRender',
        'Product Render',
        'EXT:pxa_product_manager/Resources/Public/Icons/Extension.svg',
        'Product Manager',
        '',
        'FILE:EXT:pxa_product_manager/Configuration/FlexForms/flexform_product_render.xml'
    );

    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
        'pxa_product_manager',
        'CustomProductList',
        'Custom Product List',
        'EXT:pxa_product_manager/Resources/Public/Icons/Extension.svg',
        'Product Manager',
        '',
        'FILE:EXT:pxa_product_manager/Configuration/FlexForms/flexform_product_custom_list.xml'
    );
})();
