<?php

declare(strict_types=1);

namespace Pixelant\PxaProductManager\Backend\FormEngine\FieldWizard;

use Pixelant\PxaProductManager\Utility\TcaUtility;
use Pixelant\PxaProductManager\Domain\Repository\AttributeValueRepository;
use Pixelant\PxaProductManager\Domain\Repository\ProductRepository;
use Pixelant\PxaProductManager\Utility\AttributeUtility;
use TYPO3\CMS\Backend\Form\AbstractNode;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Imaging\IconSize;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Implements a field wizard that displays the parent product's field values.
 */
class ParentValueFieldWizard extends AbstractNode
{
    /**
     * @var array[] '<productId>' => ['<attributeId>' => [<attributeValueRecord>]]
     */
    protected static array $attributeValueCache = [];

    /**
     * @return array
     */
    public function render(): array
    {
        $result = $this->initializeResultArray();
        $tableName = (string)($this->data['tableName'] ?? '');
        $record = (array)($this->data['databaseRow'] ?? []);

        if ($tableName === ProductRepository::TABLE_NAME) {
            $fieldName = (string)($this->data['fieldName'] ?? '');
            $parentRecord = [];
            if (isset($record['parent']) && is_array($record['parent'])) {
                $parentRecord = (array)($record['parent'][0]['row'] ?? []);
            }
        } elseif ($tableName === AttributeValueRepository::TABLE_NAME) {
            $fieldName = 'value';
            $parentRecord = AttributeUtility::findAttributeValue(
                (int)($record['product'] ?? 0),
                (int)TcaUtility::getAttributeUid($record['attribute'] ?? 0)
            );
        } else {
            return $result;
        }

        if (!$parentRecord) {
            return $result;
        }

        $label = LocalizationUtility::translate(
            'LLL:EXT:pxa_product_manager/Resources/Private/Language/locallang_be.xlf:formengine.parentvalue.label'
        );

        $icon = GeneralUtility::makeInstance(IconFactory::class)->getIconForRecord(
            $tableName,
            $record,
            IconSize::SMALL
        );

        $processedParentValue = BackendUtility::getProcessedValueExtra(
            $tableName,
            $fieldName,
            $parentRecord[$fieldName] ?? ''
        );

        $html = '<div class="bg-info" style="padding: .2em" title="' . htmlspecialchars($label) . '">';
        $html .= $icon . ' <strong>' . htmlspecialchars($label) . ':</strong> ';
        $html .= htmlspecialchars((string)$processedParentValue);
        $html .= '</div>';

        $result['html'] = $html;

        return $result;
    }
}
