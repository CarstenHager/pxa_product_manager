<?php

declare(strict_types=1);

namespace Pixelant\PxaProductManager\Service\Resource;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use Pixelant\PxaProductManager\Domain\Resource\ResourceInterface;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class ResourceConverter
{
    /**
     * Convert entity to array using resource.
     *
     * @param AbstractEntity $entity
     * @param string|null $resource
     * @return array
     */
    public function convert(AbstractEntity $entity, ?string $resource = null): array
    {
        $resource ??= $this->translateEntityNameToResourceName($entity);
        /** @var ResourceInterface $resourceInstance */
        $resourceInstance = GeneralUtility::makeInstance($resource, $entity);

        if ($resourceInstance instanceof \Pixelant\PxaProductManager\Domain\Resource\Product) {
            $resourceInstance->injectConfigurationManagerInterface(GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::class));
            $resourceInstance->injectImageService(GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Service\ImageService::class));
            $resourceInstance->injectUrlBuilderServiceInterface(GeneralUtility::makeInstance(\Pixelant\PxaProductManager\Service\Url\UrlBuilderServiceInterface::class));
            $resourceInstance->injectSettingsReader(GeneralUtility::makeInstance(\Pixelant\PxaProductManager\Configuration\Site\SettingsReader::class));
        }

        return $resourceInstance->toArray();
    }

    /**
     * Convert many entities.
     *
     * @param AbstractEntity[] $entities
     * @param string $resource
     * @return array
     */
    public function covertMany(array $entities, ?string $resource = null)
    {
        return array_map(fn (AbstractEntity $entity) => $this->convert($entity, $resource), $entities);
    }

    /**
     * Translate entity to its corresponding resource.
     *
     * @param AbstractEntity $entity
     * @return string
     */
    protected function translateEntityNameToResourceName(AbstractEntity $entity): string
    {
        $entityName = get_class($entity);
        $resource = str_replace('\\Model\\', '\\Resource\\', $entityName);

        // If entity was extended, but no resource exist, fallback to original
        if (!class_exists($resource)) {
            [$lastPart] = explode('\\', strrev($entityName), 2);

            $resource = 'Pixelant\\PxaProductManager\\Domain\\Resource\\' . strrev($lastPart);
        }

        return $resource;
    }
}
