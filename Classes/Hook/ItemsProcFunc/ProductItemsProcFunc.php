<?php

declare(strict_types=1);

namespace Pixelant\PxaProductManager\Hook\ItemsProcFunc;

use Pixelant\PxaProductManager\Domain\Repository\AttributeRepository;
use Pixelant\PxaProductManager\Domain\Repository\AttributeSetRepository;
use Pixelant\PxaProductManager\Domain\Repository\ProductRepository;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Database\RelationHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Miscellaneous methods to provide data to the TCA.
 */
class ProductItemsProcFunc extends GeneralItemsProcFunc
{
    /**
     * Returns an array of ["field_name", "LLL:field label"] pairs from the Products table. Includes attributes.
     *
     * $configuration[itemsProcConfig] contains configuration:
     *
     *     `exclude` comma separated list of fields to exclude
     *
     * @param array $configuration
     * @return array
     */
    public function getProductFields(array $configuration): array
    {
        $configuration['config']['itemsProcConfig']['table'] = ProductRepository::TABLE_NAME;

        $fields = $this->getFieldsForTable($configuration);

        $attributesFieldTca = BackendUtility::getTcaFieldConfiguration(
            AttributeSetRepository::TABLE_NAME,
            'attributes'
        );

        $attributeSets = $configuration['row']['attribute_sets'] ?? [];
        if (!is_array($attributeSets)) {
            $attributeSets = GeneralUtility::intExplode(',', (string)$attributeSets, true);
        }
        foreach ($attributeSets as $attributeSetId) {
            /** @var RelationHandler $relationHandler */
            $relationHandler = GeneralUtility::makeInstance(RelationHandler::class);
            $relationHandler->start(
                '',
                AttributeRepository::TABLE_NAME,
                $attributesFieldTca['MM'],
                $attributeSetId,
                AttributeSetRepository::TABLE_NAME,
                $attributesFieldTca
            );

            $records = $relationHandler->getFromDB()[AttributeRepository::TABLE_NAME] ?? [];
            foreach ($records as $attribute) {
                $fields['items'][] = [
                    'label' => ($attribute['label'] ?? '') ?: ($attribute['name'] ?? ''),
                    'value' => 'attribute.' . $attribute['uid'],
                    'icon' => $GLOBALS['TCA'][AttributeRepository::TABLE_NAME]['ctrl']['iconfile'] ?? '',
                ];
            }
        }

        return $fields;
    }
}
