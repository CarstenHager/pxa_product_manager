<?php

declare(strict_types=1);

namespace Pixelant\PxaProductManager\Attributes\ConfigurationProvider;

use Pixelant\PxaProductManager\Utility\AttributeUtility;

/**
 * Selectbox TCA.
 */
class SelectBoxProvider extends AbstractProvider
{
    /**
     * {@inheritdoc}
     */
    protected function overrideWithSpecificTca(array $tca): array
    {
        if ($this->isRequired()) {
            $tca['config']['minitems'] = 1;
        }

        $options = [];

        $attributeOptions = AttributeUtility::findAttributeOptions((int)($this->attribute['uid'] ?? 0), 'uid, value') ?? [];
        foreach ($attributeOptions as $option) {
            $options[] = [
                'label' => (string)($option['value'] ?? ''),
                'value' => (int)($option['uid'] ?? 0),
            ];
        }

        if (empty($options)) {
            // @codingStandardsIgnoreStart
            $tca['label'] = (string)($tca['label'] ?? '')
                . ' (This attribute has no options. Please configure the attribute and add some options to it.)';
            // @codingStandardsIgnoreEnd
        }

        $tca['config']['items'] = $options;

        return $tca;
    }
}
