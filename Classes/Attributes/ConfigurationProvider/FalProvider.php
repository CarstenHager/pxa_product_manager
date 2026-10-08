<?php

declare(strict_types=1);

namespace Pixelant\PxaProductManager\Attributes\ConfigurationProvider;

use Pixelant\PxaProductManager\Domain\Model\Attribute;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/**
 * Fal configuration.
 */
class FalProvider implements ProviderInterface
{
    /**
     * @var array
     */
    protected array $attribute;

    /**
     * @param array $attribute
     */
    public function __construct(array $attribute)
    {
        $this->attribute = $attribute;
    }

    /**
     * Return TCA configuration.
     *
     * @return array
     */
    public function get(): array
    {
        if (($this->attribute['type'] ?? '') === Attribute::ATTRIBUTE_TYPE_IMAGE) {
            $allowedFileTypes = (string)($GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext'] ?? '');
            $label = 'LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:images.addFileReference';
        } else {
            $allowedFileTypes = '';
            $label = 'LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:media.addFileReference';
        }

        $configuration = $this->getFalFieldTCAConfiguration(
            $label,
            $allowedFileTypes
        );

        $configuration['label'] = (string)($this->attribute['name'] ?? 'Value');

        return $configuration;
    }

    /**
     * Fal dynamic configuration.
     *
     * @param string $addNewLabel
     * @param string $allowedFileExtensions
     * @param string $disallowedFileExtensions
     * @return array
     */
    protected function getFalFieldTCAConfiguration(
        string $addNewLabel = '',
        string $allowedFileExtensions = '',
        string $disallowedFileExtensions = ''
    ): array {
        return [
            'exclude' => false,
            'label' => '',
            // @codingStandardsIgnoreStart
            'config' => \Pixelant\PxaProductManager\Utility\TcaFileFieldConfiguration::get(
                'value',
                [
                    'appearance' => [
                        'createNewRelationLinkTitle' => $addNewLabel,
                        'showPossibleLocalizationRecords' => false,
                        'showRemovedLocalizationRecords' => true,
                        'showAllLocalizationLink' => false,
                        'showSynchronizationLink' => false,
                        'collapseAll' => true,
                    ],
                    'foreign_match_fields' => [
                        'fieldname' => 'value',
                        'tablenames' => 'tx_pxaproductmanager_domain_model_attributevalue',
                    ],
                    'behaviour' => [
                        'allowLanguageSynchronization' => true,
                    ],
                    'overrideChildTca' => [
                        /*'columns' => [
                            'pxa_attribute' => [
                                'config' => [
                                    'default' => $this->attribute,
                                ],
                            ],
                        ],*/
                        'types' => [
                            \TYPO3\CMS\Core\Resource\FileType::UNKNOWN->value => [
                                'showitem' => '
                            --palette--;LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;pxaProductManagerAttributePalette,
                            --palette--;LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.basicoverlayPalette;basicoverlayPalette,
                            --palette--;;filePalette',
                            ],
                            \TYPO3\CMS\Core\Resource\FileType::TEXT->value => [
                                'showitem' => '
                            --palette--;LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;pxaProductManagerAttributePalette,
                            --palette--;LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.basicoverlayPalette;basicoverlayPalette,
                            --palette--;;filePalette',
                            ],
                            \TYPO3\CMS\Core\Resource\FileType::IMAGE->value => [
                                'showitem' => '
                            --palette--;LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;pxaProductManagerAttributePalette,
                            --palette--;LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                            --palette--;;filePalette',
                            ],
                            \TYPO3\CMS\Core\Resource\FileType::AUDIO->value => [
                                'showitem' => '
                            --palette--;LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;pxaProductManagerAttributePalette,
                            --palette--;LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.audioOverlayPalette;audioOverlayPalette,
                            --palette--;;filePalette',
                            ],
                            \TYPO3\CMS\Core\Resource\FileType::VIDEO->value => [
                                'showitem' => '
                            --palette--;LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;pxaProductManagerAttributePalette,
                            --palette--;LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.videoOverlayPalette;videoOverlayPalette,
                            --palette--;;filePalette',
                            ],
                            \TYPO3\CMS\Core\Resource\FileType::APPLICATION->value => [
                                'showitem' => '
                            --palette--;LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;pxaProductManagerAttributePalette,
                            --palette--;LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.basicoverlayPalette;basicoverlayPalette,
                            --palette--;;filePalette',
                            ],
                        ],
                    ],
                    'maxitems' => 99,
                ],
                $allowedFileExtensions,
                $disallowedFileExtensions
            ),
            // @codingStandardsIgnoreEnd
        ];
    }
}
