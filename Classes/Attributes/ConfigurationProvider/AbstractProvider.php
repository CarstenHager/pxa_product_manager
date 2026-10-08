<?php

declare(strict_types=1);

namespace Pixelant\PxaProductManager\Attributes\ConfigurationProvider;

use Pixelant\PxaProductManager\Domain\Model\Attribute;

/**
 * Abstract general configuration.
 */
abstract class AbstractProvider implements ProviderInterface
{
    /**
     * @var array Attribute row
     */
    protected array $attribute;

    /**
     * @var array
     */
    protected array $tca = [
        Attribute::ATTRIBUTE_TYPE_INPUT => [
            'exclude' => false,
            'label' => '',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
            ],
        ],

        Attribute::ATTRIBUTE_TYPE_TEXT => [
            'exclude' => false,
            'label' => '',
            'config' => [
                'type' => 'text',
                'cols' => '48',
                'rows' => '8',
                'eval' => 'trim',
            ],
        ],

        Attribute::ATTRIBUTE_TYPE_CHECKBOX => [
            'exclude' => false,
            'label' => '',
            'config' => [
                'type' => 'check',
                'items' => [],
            ],
        ],

        Attribute::ATTRIBUTE_TYPE_DROPDOWN => [
            'exclude' => false,
            'label' => '',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [],
                'size' => 1,
                'maxitems' => 1,
            ],
        ],

        Attribute::ATTRIBUTE_TYPE_MULTISELECT => [
            'exclude' => false,
            'label' => '',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectMultipleSideBySide',
                'items' => [],
                'size' => 10,
                'maxitems' => 99,
                'multiple' => 0,
            ],
        ],

        Attribute::ATTRIBUTE_TYPE_DATETIME => [
            'exclude' => false,
            'label' => '',
            'config' => [
                'type' => 'datetime',
                'format' => 'datetime',
                'default' => 0,
            ],
        ],

        Attribute::ATTRIBUTE_TYPE_LINK => [
            'exclude' => false,
            'label' => '',
            'config' => [
                'type' => 'link',
                'size' => '30',
                'max' => '256',
                'softref' => 'typolink',
            ],
        ],

        Attribute::ATTRIBUTE_TYPE_LABEL => [
            'exclude' => false,
            'label' => '',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
            ],
        ],
    ];

    /**
     * @param array $attribute
     */
    public function __construct(array $attribute)
    {
        $this->attribute = $attribute;
    }

    /**
     * Return attribute TCA.
     *
     * @return array
     */
    public function get(): array
    {
        return $this->overrideWithSpecificTca(
            $this->getAttributeConfiguration()
        );
    }

    /**
     * Initial TCA configuration.
     *
     * @return array
     */
    protected function getAttributeConfiguration(): array
    {
        $configuration = $this->tca[$this->attribute['type'] ?? Attribute::ATTRIBUTE_TYPE_INPUT]
            ?? $this->tca[Attribute::ATTRIBUTE_TYPE_INPUT];
        $configuration['label'] = (string)($this->attribute['name'] ?? 'Value');

        if (isset($this->attribute['default_value'])) {
            $configuration['config'] ??= [];
            $configuration['config']['default'] = $this->attribute['default_value'];
        }

        return $configuration;
    }

    /**
     * Shortcut method.
     *
     * @return bool
     */
    protected function isRequired(): bool
    {
        return (bool)($this->attribute['required'] ?? false);
    }

    /**
     * Specific configuration for attribute.
     *
     * @param array $tca
     * @return array
     */
    abstract protected function overrideWithSpecificTca(array $tca): array;
}
