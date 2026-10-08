<?php

declare(strict_types=1);

namespace Pixelant\PxaProductManager\Backend\FormEngine\FieldWizard;

use Pixelant\PxaProductManager\Utility\TcaUtility;
use Pixelant\PxaProductManager\Domain\Repository\AttributeValueRepository;
use TYPO3\CMS\Backend\Form\AbstractNode;
use TYPO3\CMS\Core\Utility\MathUtility;

/**
 * Implements a field wizard that displays the parent product's field values.
 */
class HiddenAttributeTypeValueFieldWizard extends AbstractNode
{
    /**
     * @return array
     */
    public function render(): array
    {
        $result = $this->initializeResultArray();
        $databaseRow = (array)($this->data['databaseRow'] ?? []);
        $uid = $databaseRow['uid'] ?? '';
        if (is_array($uid)) {
            $uid = $uid[0] ?? '';
        }

        // We only do this on NEW records
        if (MathUtility::canBeInterpretedAsInteger($uid)) {
            return $result;
        }

        $html = '<input type="hidden" ';
        $html .= 'name="data[' . AttributeValueRepository::TABLE_NAME . ']';
        $html .= '[' . htmlspecialchars((string)$uid) . '][attribute]" ';
        $html .= 'value="' . htmlspecialchars((string)TcaUtility::getAttributeUid($databaseRow['attribute'] ?? 0)) . '" ';
        $html .= '/>';

        $html .= '<input type="hidden" ';
        $html .= 'name="data[' . AttributeValueRepository::TABLE_NAME . ']';
        $html .= '[' . htmlspecialchars((string)$uid) . '][sys_language_uid]" ';
        $language = $databaseRow['sys_language_uid'] ?? 0;
        if (is_array($language)) {
            $language = $language[0] ?? 0;
        }
        $html .= 'value="' . htmlspecialchars((string)(int)$language) . '" ';
        $html .= '/>';

        $result['html'] = $html;

        return $result;
    }
}
