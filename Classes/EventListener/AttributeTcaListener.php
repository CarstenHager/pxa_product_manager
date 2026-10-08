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
        $GLOBALS['TCA'][$table]['types'] = ['0' => ['showitem' => 'value']];
        foreach ($attributes as $attribute) {
            if ((int)$attribute['uid'] <= 0) {
                continue;
            }
            $GLOBALS['TCA'][$table]['types'][(string)$attribute['uid']] = [
                'showitem' => 'value',
                'columnsOverrides' => [
                    'value' => ConfigurationProviderFactory::create((int)$attribute['uid'], $attribute)->get(),
                ],
            ];
        }
    }
}
