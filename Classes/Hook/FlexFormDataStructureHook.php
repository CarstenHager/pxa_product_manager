<?php

declare(strict_types=1);

namespace Pixelant\PxaProductManager\Hook;

use Pixelant\PxaProductManager\Configuration\Flexform\Registry;
use Pixelant\PxaProductManager\Configuration\Flexform\StructureLoader;
use TYPO3\CMS\Core\Configuration\Event\AfterFlexFormDataStructureIdentifierInitializedEvent;
use TYPO3\CMS\Core\Configuration\Event\AfterFlexFormDataStructureParsedEvent;
use TYPO3\CMS\Core\Configuration\FlexForm\FlexFormTools;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class FlexFormDataStructureHook.
 */
final class FlexFormDataStructureHook
{
    /**
     * Flexform identifier.
     *
     * @var string
     */
    protected const LEGACY_IDENTIFIER = 'pxaproductmanager_pi1,list';

    /**
     * @var FlexFormTools
     */
    protected FlexFormTools $service;

    /**
     * @var Registry
     */
    protected Registry $registry;

    /**
     * @var StructureLoader
     */
    protected StructureLoader $loader;

    public function __construct(
        ?FlexFormTools $service = null,
        ?Registry $registry = null,
        ?StructureLoader $loader = null
    ) {
        $this->service = $service ?? GeneralUtility::makeInstance(FlexFormTools::class);
        $this->registry = $registry ?? GeneralUtility::makeInstance(Registry::class);
        $this->loader = $loader ?? GeneralUtility::makeInstance(StructureLoader::class);
    }

    /**
     * Carry the selected Product Manager action from the source record into the
     * identifier used for the subsequent data-structure parsing event.
     *
     * TYPO3 removed the old FlexFormTools hook in v12. The namespaced custom
     * identifier key is deliberately only an in-memory parsing hint; it is not
     * used by the core to resolve the actual TCA data structure.
     */
    public function modifyDataStructureIdentifier(
        AfterFlexFormDataStructureIdentifierInitializedEvent $event
    ): void {
        $identifier = $event->getIdentifier();
        if (!$this->isProductManagerIdentifier($identifier)) {
            return;
        }

        $row = $event->getRow();
        if (!is_string($row['pi_flexform'] ?? null) || trim($row['pi_flexform']) === '') {
            return;
        }

        $settings = $this->service->convertFlexFormContentToArray($row['pi_flexform']);
        $action = trim((string)($settings['switchableControllerActions'] ?? ''));
        if ($action === '') {
            return;
        }

        $identifier['pxaProductManagerAction'] = $action;
        $event->setIdentifier($identifier);
    }

    /**
     * Add the registered actions and merge the selected action's parts into the
     * parsed FlexForm data structure.
     */
    public function modifyDataStructure(AfterFlexFormDataStructureParsedEvent $event): void
    {
        $identifier = $event->getIdentifier();
        if (!$this->isProductManagerIdentifier($identifier)) {
            return;
        }

        $dataStructure = $this->addSwitchableControllerActions($event->getDataStructure());
        $action = trim((string)($identifier['pxaProductManagerAction'] ?? ''));
        if ($action !== '') {
            $dataStructure = $this->loader->loadDefaultDataStructure($dataStructure);
            $dataStructure = $this->loader->loadActionDataStructure(
                $dataStructure,
                $this->registry->getSwitchableControllerActionConfiguration($action)
            );
        }

        $event->setDataStructure($dataStructure);
    }

    /**
     * Return data structure with actions.
     *
     * @param array $dataStructure
     * @return array
     */
    protected function addSwitchableControllerActions(array $dataStructure): array
    {
        if (
            !isset(
                $dataStructure['sheets']['sDEF']['ROOT']['el']['switchableControllerActions']
                ['TCEforms']['config']['items']
            )
            || !is_array(
                $dataStructure['sheets']['sDEF']['ROOT']['el']['switchableControllerActions']
                ['TCEforms']['config']['items']
            )
        ) {
            return $dataStructure;
        }
        $items = &$dataStructure['sheets']['sDEF']['ROOT']['el']['switchableControllerActions']['TCEforms']['config']['items'];

        foreach ($this->registry->getAllRegisteredActions() as $action) {
            if (!is_array($action) || empty($action['action'])) {
                continue;
            }
            $items[] = [
                (string)($action['label'] ?? $action['action']),
                (string)$action['action'],
            ];
        }

        return $dataStructure;
    }

    /**
     * Set last action from DB row flexform xml.
     *
     * @param array $row
     */
    protected function isProductManagerIdentifier(array $identifier): bool
    {
        $key = (string)($identifier['dataStructureKey'] ?? '');

        return $key === self::LEGACY_IDENTIFIER || str_starts_with($key, 'pxaproductmanager_');
    }
}
