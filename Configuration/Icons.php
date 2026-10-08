<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider;

return [
    'ext-pxa-product-manager-wizard-icon' => [
        'provider' => SvgIconProvider::class,
        'source' => 'EXT:pxa_product_manager/Resources/Public/Icons/Svg/package.svg',
    ],
    'apps-pagetree-productdisplay-default' => [
        'provider' => SvgIconProvider::class,
        'source' => 'EXT:pxa_product_manager/Resources/Public/Icons/Svg/T3Icons/apps/apps-pagetree-productdisplay-default.svg',
    ],
    'apps-pagetree-productdisplay-hideinmenu' => [
        'provider' => SvgIconProvider::class,
        'source' => 'EXT:pxa_product_manager/Resources/Public/Icons/Svg/T3Icons/apps/apps-pagetree-productdisplay-hideinmenu.svg',
    ],
];
