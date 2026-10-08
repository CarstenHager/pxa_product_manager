<?php

declare(strict_types=1);

defined('TYPO3') || die;

foreach (['product', 'attribute', 'attributeset', 'attributevalue', 'option', 'link', 'filter', 'producttype'] as $table) {
    $GLOBALS['TCA']['tx_pxaproductmanager_domain_model_' . $table]['ctrl']['security']['ignorePageTypeRestriction'] = true;
}
