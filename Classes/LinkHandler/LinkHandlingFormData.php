<?php

namespace Pixelant\PxaProductManager\LinkHandler;

use Pixelant\PxaProductManager\Translate\CanTranslateInBackend;
use TYPO3\CMS\Backend\Form\Event\ModifyLinkExplanationEvent;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Imaging\IconSize;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class LinkHandlingFormData.
 */
class LinkHandlingFormData
{
    use CanTranslateInBackend;

    /**
     * @var IconFactory
     */
    protected IconFactory $iconFactory;

    /**
     * Initialization.
     *
     * @param IconFactory|null $iconFactory
     */
    public function __construct(?IconFactory $iconFactory = null)
    {
        $this->iconFactory = $iconFactory ?? GeneralUtility::makeInstance(IconFactory::class);
    }

    public function __invoke(ModifyLinkExplanationEvent $event): void
    {
        $linkData = $event->getLinkData();
        if (($linkData['type'] ?? '') !== 'pxappm') {
            return;
        }
        $table = isset($linkData['product']) ? 'tx_pxaproductmanager_domain_model_product' : 'sys_category';
        $id = (int)($linkData['product'] ?? $linkData['category'] ?? 0);
        $row = BackendUtility::getRecord($table, $id);
        $event->setLinkExplanation(array_replace($event->getLinkExplanation(), [
            'text' => $row === null ? 'Record not found [' . $id . ']' : BackendUtility::getRecordTitle($table, $row) . ' [' . $id . ']',
            'icon' => $this->iconFactory->getIconForRecord($table, $row ?? [], IconSize::SMALL)->render(),
        ]));
    }
}
