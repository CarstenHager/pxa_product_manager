<?php

declare(strict_types=1);

namespace Pixelant\PxaProductManager\ViewHelpers;

use Pixelant\PxaProductManager\Domain\Resource\ResourceInterface;
use Pixelant\PxaProductManager\Service\Resource\ResourceConverter;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class ResourceEncodeViewHelper extends AbstractViewHelper
{
    /**
     * @var ResourceConverter
     */
    protected static ?ResourceConverter $converter = null;

    /**
     * View helper arguments.
     */
    public function initializeArguments(): void
    {
        $this->registerArgument('entity', AbstractEntity::class, 'Entity that should be convert to resource');
        $this->registerArgument('resource', 'string', 'Override resource class name');
    }

    /**
     * Encode the resource representation as JSON.
     */
    public function render(): string
    {
        /** @var AbstractEntity $entity */
        $entity = $this->arguments['entity'] ?? $this->renderChildren();
        if (!$entity instanceof AbstractEntity) {
            return '{}';
        }

        /** @var ResourceInterface $resource */
        $resourceArray = static::getConverter()->convert($entity, $this->arguments['resource'] ?? null);

        return (string)json_encode($resourceArray);
    }

    /**
     * @return ResourceConverter
     */
    protected static function getConverter(): ResourceConverter
    {
        if (static::$converter === null) {
            static::$converter = GeneralUtility::makeInstance(ResourceConverter::class);
        }

        return self::$converter;
    }
}
