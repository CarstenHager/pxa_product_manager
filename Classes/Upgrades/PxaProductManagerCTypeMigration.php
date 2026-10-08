<?php

declare(strict_types=1);

namespace Pixelant\PxaProductManager\Upgrades;

use TYPO3\CMS\Core\Attribute\UpgradeWizard;
use TYPO3\CMS\Core\Upgrades\AbstractListTypeToCTypeUpdate;

/**
 * Migrates Product Manager content elements from the removed list/list_type
 * representation to their dedicated TYPO3 v14 CType values.
 */
#[UpgradeWizard('pxaProductManagerListTypeToCType')]
final class PxaProductManagerCTypeMigration extends AbstractListTypeToCTypeUpdate
{
    protected function getListTypeToCTypeMapping(): array
    {
        return [
            'pxaproductmanager_productlist' => 'pxaproductmanager_productlist',
            'pxaproductmanager_productshow' => 'pxaproductmanager_productshow',
            'pxaproductmanager_productrender' => 'pxaproductmanager_productrender',
            'pxaproductmanager_customproductlist' => 'pxaproductmanager_customproductlist',
        ];
    }

    public function getTitle(): string
    {
        return 'Migrate Product Manager plugins to CType';
    }

    public function getDescription(): string
    {
        return 'Converts existing Product Manager tt_content records from list/list_type to the TYPO3 v14 CType representation.';
    }
}
