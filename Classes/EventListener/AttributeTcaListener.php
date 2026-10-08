<?php

declare(strict_types=1);

namespace Pixelant\PxaProductManager\EventListener;

use Doctrine\DBAL\Exception\TableNotFoundException;
use Pixelant\PxaProductManager\Attributes\ConfigurationProvider\ConfigurationProviderFactory;
use Pixelant\PxaProductManager\Utility\AttributeUtility;
use TYPO3\CMS\Core\Core\Event\BootCompletedEvent;

/** Ergänzt dynamische Attribute erst nach dem Bootstrap, wenn Datenbankzugriffe erlaubt sind. */
final class AttributeTcaListener
{
    public function __invoke(BootCompletedEvent $event): void
    {
        try {
            $attributes = AttributeUtility::findAllAttributes();
        } catch (TableNotFoundException $exception) {
            // Erstinstallation: Die Tabellen werden erst durch die Datenbankanalyse angelegt.
            return;
        }
        $table = 'tx_pxaproductmanager_domain_model_attributevalue';
        $GLOBALS['TCA'][$table] ??= [];
        $GLOBALS['TCA'][$table]['types'] = ['0' => ['showitem' => 'value']];
        foreach ($attributes as $attribute) {
            if (!is_array($attribute) || (int)($attribute['uid'] ?? 0) <= 0) {
                continue;
            }
            try {
                $valueConfiguration = ConfigurationProviderFactory::create((int)$attribute['uid'], $attribute)->get();
            } catch (\UnexpectedValueException) {
                // Keep the base type available if an old installation contains
                // an attribute type no longer known by this extension version.
                $valueConfiguration = [
                    'exclude' => false,
                    'label' => (string)($attribute['name'] ?? 'Value'),
                    'config' => ['type' => 'input'],
                ];
            }
            // FormEngine expects every dynamically overridden column to provide
            // a label. Keep the fallback here as a last line of defence for
            // custom providers supplied by integrators.
            $valueConfiguration['label'] ??= (string)($attribute['name'] ?? 'Value');
            $valueConfiguration['config'] ??= ['type' => 'input'];
            $GLOBALS['TCA'][$table]['types'][(string)$attribute['uid']] = [
                'showitem' => 'value',
                'columnsOverrides' => [
                    'value' => $valueConfiguration,
                ],
            ];
        }
    }
}
