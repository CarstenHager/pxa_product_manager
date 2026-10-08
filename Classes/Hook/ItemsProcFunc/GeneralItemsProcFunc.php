<?php

declare(strict_types=1);

namespace Pixelant\PxaProductManager\Hook\ItemsProcFunc;

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Miscellaneous methods to provide data to the TCA.
 */
class GeneralItemsProcFunc
{
    /**
     * Returns an array of ["field_name", "LLL:field label"] pairs from a table.
     *
     * $configuration[itemsProcConfig] contains configuration:
     *
     *     `table` (required) Name of the table to return fields for
     *     `exclude` comma separated list of fields to exclude
     *
     * @param array $configuration
     * @return array
     */
    public function getFieldsForTable(array $configuration): array
    {
        $tableName = (string)($configuration['config']['itemsProcConfig']['table'] ?? '');
        $table = $GLOBALS['TCA'][$tableName] ?? [];
        $columns = $table['columns'] ?? [];
        $excludeFields = array_filter([
            $table['ctrl']['languageField'] ?? '',
            $table['ctrl']['transOrigPointerField'] ?? '',
            $table['ctrl']['type'] ?? '',
        ]);
        $exclude = (string)($configuration['config']['itemsProcConfig']['exclude'] ?? '');
        $excludeFields = array_merge($excludeFields, GeneralUtility::trimExplode(',', $exclude, true));
        $configuration['items'] = [];
        foreach ($columns as $fieldName => $column) {
            if (($column['config']['type'] ?? '') === 'passthrough' || in_array($fieldName, $excludeFields, true)) {
                continue;
            }
            $configuration['items'][] = [
                'label' => $column['label'] ?? $fieldName,
                'value' => $fieldName,
                'icon' => $table['ctrl']['iconfile'] ?? '',
            ];
        }
        return $configuration;
    }
}
