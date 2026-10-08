<?php

declare(strict_types=1);

namespace Pixelant\PxaProductManager\Domain\Resource;

class Filter extends AbstractResource
{
    /**
     * Convert the filter entity to the historic frontend API structure.
     *
     * The database fields use snake_case while the Extbase entity exposes
     * guiType and guiState. Resolve those two properties explicitly so TYPO3
     * 12 does not silently turn a checkbox filter into a select filter.
     *
     * @param array|null $additionalProperties
     * @return array
     */
    protected function extractProperties(?array $additionalProperties = null): array
    {
        $result = parent::extractProperties($additionalProperties);
        $result['gui_type'] = $result['guiType'] ?? '';
        $result['gui_state'] = $result['guiState'] ?? '';
        unset($result['guiType'], $result['guiState']);

        return $result;
    }

    /**
     * @return array
     */
    protected function extractableProperties(): array
    {
        return [
            'uid',
            'name',
            'label',
            'type',
            'guiType',
            'guiState',
            'options',
            'attributeUid',
            'conjunction',
        ];
    }
}
