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

        // We only do this on NEW records
        if (MathUtility::canBeInterpretedAsInteger($this->data['databaseRow']['uid'])) {
            return $result;
        }

        $html = '<input type="hidden" ';
        $html .= 'name="data[' . AttributeValueRepository::TABLE_NAME . ']';
        $html .= '[' . $this->data['databaseRow']['uid'] . '][attribute]" ';
        $html .= 'value="' . htmlspecialchars((string)TcaUtility::getAttributeUid($this->data['databaseRow']['attribute'] ?? 0)) . '" ';
        $html .= '/>';

        $html .= '<input type="hidden" ';
        $html .= 'name="data[' . AttributeValueRepository::TABLE_NAME . ']';
        $html .= '[' . $this->data['databaseRow']['uid'] . '][sys_language_uid]" ';
        $language = $this->data['databaseRow']['sys_language_uid'] ?? 0;
        if (is_array($language)) {
            $language = $language[0] ?? 0;
        }
        $html .= 'value="' . htmlspecialchars((string)(int)$language) . '" ';
        $html .= '/>';

        $result['html'] = $html;

        return $result;
    }
}
