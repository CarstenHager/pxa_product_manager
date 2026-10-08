<?php

declare(strict_types=1);

namespace Pixelant\PxaProductManager\ViewHelpers;

use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class MetaTagViewHelper extends AbstractViewHelper
{
    /**
     * Arguments.
     */
    public function initializeArguments(): void
    {
        $this->registerArgument('type', 'string', 'Type of meta tag', false, 'name');
        $this->registerArgument('content', 'string', 'Content of meta tag', false, null);
        $this->registerArgument('name', 'string', 'Meta tag name', true);
    }

    /**
     * Render and register a meta tag.
     */
    public function render(): string
    {
        // Fluid normally supplies all registered arguments.  Keep the view
        // helper defensive nevertheless: malformed/legacy templates must not
        // turn a missing optional argument into a PHP warning on TYPO3 14.
        $type = (string)($this->arguments['type'] ?? 'name');
        $name = (string)($this->arguments['name'] ?? '');
        $content = trim(strip_tags((string)($this->arguments['content'] ?? $this->renderChildren())));

        if ($name !== '' && $content !== '') {
            static::getPageRenderer()->setMetaTag(
                $type,
                $name,
                $content,
                [],
                true
            );
        }

        return '';
    }

    /**
     * @return PageRenderer
     */
    protected static function getPageRenderer(): PageRenderer
    {
        return GeneralUtility::makeInstance(PageRenderer::class);
    }
}
