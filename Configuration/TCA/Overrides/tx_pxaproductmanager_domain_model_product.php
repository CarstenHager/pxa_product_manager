<?php

defined('TYPO3') || die();

$table = 'tx_pxaproductmanager_domain_model_product';
$GLOBALS['TCA'][$table]['columns']['categories'] = [
    'exclude' => true,
    'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_category',
    'config' => [
        'type' => 'category',
        'relationship' => 'manyToMany',
        'foreign_table_where' => \Pixelant\PxaProductManager\Utility\TcaUtility::getCategoriesTCAWhereClause()
            . ' AND sys_category.sys_language_uid IN (-1, 0)',
    ],
];
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes($table, 'categories');
