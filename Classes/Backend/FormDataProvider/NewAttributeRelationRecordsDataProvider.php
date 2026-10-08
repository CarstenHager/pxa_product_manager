<?php

declare(strict_types=1);

namespace Pixelant\PxaProductManager\Backend\FormDataProvider;

use Pixelant\PxaProductManager\Utility\TcaUtility;
use Pixelant\PxaProductManager\Domain\Collection\CanCreateCollection;
use Pixelant\PxaProductManager\Domain\Repository\AttributeRepository;
use Pixelant\PxaProductManager\Domain\Repository\ProductRepository;
use Pixelant\PxaProductManager\FlashMessage\BackendFlashMessage;
use Pixelant\PxaProductManager\Translate\CanTranslateInBackend;
use Pixelant\PxaProductManager\Utility\AttributeUtility;
use TYPO3\CMS\Backend\Form\FormDataCompiler;
use TYPO3\CMS\Backend\Form\FormDataGroup\TcaDatabaseRecord;
use TYPO3\CMS\Backend\Form\FormDataProviderInterface;
use TYPO3\CMS\Backend\Form\InlineStackProcessor;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper;

/**
 * Adds any missing attributes to a product record we're editing.
 */
class NewAttributeRelationRecordsDataProvider implements FormDataProviderInterface
{
    use CanTranslateInBackend;
    use CanCreateCollection;

    /**
     * @var DataMapper
     */
    protected DataMapper $dataMapper;

    /**
     * @var BackendFlashMessage
     */
    protected BackendFlashMessage $flashMessage;

    /**
     * @param BackendFlashMessage $flashMessage
     */
    public function __construct(?BackendFlashMessage $flashMessage = null)
    {
        $this->flashMessage = $flashMessage ?? GeneralUtility::makeInstance(BackendFlashMessage::class);
    }

    /**
     * @param array $result
     * @return array
     */
    public function addData(array $result): array
    {
        $databaseRow = (array)($result['databaseRow'] ?? []);
        if (
            ($result['tableName'] ?? '') !== ProductRepository::TABLE_NAME
            || (
                ($result['tableName'] ?? '') === ProductRepository::TABLE_NAME
                && empty($databaseRow['product_type'])
            )
        ) {
            return $result;
        }

        if (!isset($result['processedTca']['columns']['attributes_values'])
            || !is_array($result['processedTca']['columns']['attributes_values'])
        ) {
            return $result;
        }

        $productType = $databaseRow['product_type'] ?? 0;
        if (is_array($productType)) {
            $productType = $productType[0] ?? 0;
        }
        $attributes = AttributeUtility::findAttributesForProductType((int)$productType);
        $attributeUidList = array_column($attributes, 'uid');
        $attributeUidList = array_unique($attributeUidList);

        $children = $this->removeInvalidAttributeValuesChildren(
            (array)($result['processedTca']['columns']['attributes_values']['children'] ?? []),
            $attributeUidList
        );

        $children = $this->updateAttributeValues($result, $children);

        $irreChildren = [];
        foreach ($attributeUidList as $attributeId) {
            $childKey = $this->resolveAttributeValueChildArrayKeyByAttributeId(
                (int)$attributeId,
                $children
            );
            if ($childKey < 0) {
                $irreChildren[] = $this->generateNewAttributeValueChild($result, (int)$attributeId);
            } else {
                $irreChildren[] = $children[$childKey];
            }
        }

        $result['processedTca']['columns']['attributes_values']['children'] = $irreChildren;

        return $result;
    }

    /**
     * Generate New AttributeValue Child.
     *
     * @param array $result
     * @param int $attributeId
     * @return array
     */
    protected function generateNewAttributeValueChild(array $result, int $attributeId)
    {
        $parentConfig = (array)($result['processedTca']['columns']['attributes_values']['config'] ?? []);
        $childTableName = (string)($parentConfig['foreign_table'] ?? '');
        if ($childTableName === '') {
            return [];
        }

        /** @var InlineStackProcessor $inlineStackProcessor */
        $inlineStackProcessor = GeneralUtility::makeInstance(InlineStackProcessor::class);
        $inlineStackProcessor->initializeByGivenStructure((array)($result['inlineStructure'] ?? []));
        $inlineTopMostParent = (array)$inlineStackProcessor->getStructureLevel(0);

        /** @var TcaDatabaseRecord $formDataGroup */
        $formDataGroup = GeneralUtility::makeInstance(TcaDatabaseRecord::class);
        /** @var FormDataCompiler $formDataCompiler */
        $formDataCompiler = GeneralUtility::makeInstance(FormDataCompiler::class);
        // This is mostly copied from \TYPO3\CMS\Backend\Form\FormDataProvider\TcaInline
        $formDataCompilerInput = [
            'request' => $result['request'] ?? ($GLOBALS['TYPO3_REQUEST'] ?? new \TYPO3\CMS\Core\Http\ServerRequest()),
            'command' => 'new',
            'tableName' => $childTableName,
            // Give incoming returnUrl down to children so they generate a returnUrl back to
            // the originally opening record, also see "originalReturnUrl" in inline container
            // and FormInlineAjaxController
            'returnUrl' => $result['returnUrl'] ?? '',
            'isInlineChild' => true,
            'inlineStructure' => (array)($result['inlineStructure'] ?? []),
            'inlineExpandCollapseStateArray' => (array)($result['inlineExpandCollapseStateArray'] ?? []),
            'inlineFirstPid' => $result['inlineFirstPid'] ?? 0,
            'inlineParentConfig' => $parentConfig,

            // values of the current parent element
            // it is always a string either an id or new...
            'inlineParentUid' => $result['databaseRow']['uid'] ?? 0,
            'inlineParentTableName' => $result['tableName'] ?? '',
            // 'inlineParentFieldName' => $parentFieldName,
            'inlineParentFieldName' => 'attributes_values',

            // values of the top most parent element set on first level and not overridden on following levels
            'inlineTopMostParentUid' => ($result['inlineTopMostParentUid'] ?? 0) ?: ($inlineTopMostParent['uid'] ?? 0),

            // @codingStandardsIgnoreLine
            'inlineTopMostParentTableName' => ($result['inlineTopMostParentTableName'] ?? '') ?: ($inlineTopMostParent['table'] ?? ''),
            // @codingStandardsIgnoreLine
            'inlineTopMostParentFieldName' => ($result['inlineTopMostParentFieldName'] ?? '') ?: ($inlineTopMostParent['field'] ?? ''),

            'recordTypeValue' => $attributeId,
        ];

        // For foreign_selector with useCombination $mainChild is the mm record
        // and $combinationChild is the child-child. For 1:n "normal" relations,
        // $mainChild is just the normal child record and $combinationChild is empty.
        $newChild = $formDataCompiler->compile($formDataCompilerInput, $formDataGroup);
        $newChild['databaseRow'] ??= [];
        $newChild['processedTca'] ??= [];
        $newChild['processedTca']['columns'] ??= [];
        $newChild['processedTca']['columns']['value'] ??= [];
        $newChild['processedTca']['columns']['value']['config'] ??= [];
        $newChild['databaseRow']['attribute'] = [(string)$attributeId];

        // This wizard sets the attribute type
        if (($newChild['processedTca']['columns']['value']['config']['type'] ?? '') === 'inline') {
            // @codingStandardsIgnoreLine
            $newChild['processedTca']['ctrl']['container']['inline']['fieldWizard']['hiddenAttributeType']['renderType']
                = 'hiddenAttributeType';
        } else {
            // @codingStandardsIgnoreLine
            $newChild['processedTca']['columns']['value']['config']['fieldWizard']['hiddenAttributeType']['renderType']
                = 'hiddenAttributeType';
        }

        return $newChild;
    }

    /**
     * Get attribute array.
     *
     * @param int $attributeId
     * @return array
     */
    protected function getAttributeArray(int $attributeId): array
    {
        $tableName = 'tx_pxaproductmanager_domain_model_attribute';
        $uid = $attributeId;
        $record = BackendUtility::getRecordWSOL($tableName, $uid) ?? [];
        $title = BackendUtility::getRecordTitle($tableName, $record, false, false);

        return [
            'table' => 'tx_pxaproductmanager_domain_model_attribute',
            'uid' => $attributeId ?? null,
            'title' => $title,
            'row' => $record,
        ];
    }

    /**
     * Remove invalid / duplicte attribute values.
     *
     * @param array $children
     * @param array $validAttributeIdsList
     * @return array
     */
    protected function removeInvalidAttributeValuesChildren(array $children, array $validAttributeIdsList): array
    {
        $addedAttributesUidList = [];
        $attributeValueChildren = [];
        foreach ($children as $key => $child) {
            if (!is_array($child)) {
                continue;
            }
            $attributeId = (int)TcaUtility::getAttributeUid($child['databaseRow']['attribute'] ?? 0);
            if ($attributeId > 0 && in_array($attributeId, $validAttributeIdsList, true)) {
                if (!in_array($attributeId, $addedAttributesUidList, true)) {
                    $addedAttributesUidList[] = $attributeId;
                    $attributeValueChildren[$key] = $child;
                }
            }
        }

        return $attributeValueChildren;
    }

    /**
     * Resolve attribute value key by attribute id.
     *
     * @param int $attributeId
     * @param array $children
     * @return int
     */
    protected function resolveAttributeValueChildArrayKeyByAttributeId(int $attributeId, array $children): int
    {
        foreach ($children as $key => $attributeValueResult) {
            if (!is_array($attributeValueResult)) {
                continue;
            }
            if ((int)TcaUtility::getAttributeUid($attributeValueResult['databaseRow']['attribute'] ?? 0) === $attributeId) {
                return (int)$key;
            }
        }

        return -1;
    }

    /**
     * Update attribute values if needed.
     *
     * @param array $result
     * @param array $children
     * @return array
     */
    protected function updateAttributeValues(array $result, array $children): array
    {
        $productLanguageFieldName = $GLOBALS['TCA'][ProductRepository::TABLE_NAME]['ctrl']['languageField'] ?? 'sys_language_uid';
        $sysLanguageUid = isset(
            $result['databaseRow'][$productLanguageFieldName]['0']
        ) ? (int)$result['databaseRow'][$productLanguageFieldName]['0'] : 0;

        $attrLangField = $GLOBALS['TCA'][AttributeRepository::TABLE_NAME]['ctrl']['languageField'] ?? null;

        foreach ($children as $key => $attributeValueResult) {
            if (!is_array($attributeValueResult)) {
                continue;
            }
            // Make sure attributevalue has same language as edited product.
            if (is_string($attrLangField)
                && (
                isset($attributeValueResult['databaseRow'][$attrLangField][0])
                && (int)$attributeValueResult['databaseRow'][$attrLangField][0] !== $sysLanguageUid
                )
            ) {
                $children[$key]['databaseRow'][$attrLangField][0] = (string)$sysLanguageUid;
            }
        }

        return $children;
    }
}
