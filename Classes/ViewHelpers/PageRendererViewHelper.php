<?php

declare(strict_types=1);

namespace Pixelant\PxaProductManager\ViewHelpers;

use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class PageRendererViewHelper extends AbstractViewHelper
{
    /**
     * View helper arguments.
     */
    public function initializeArguments(): void
    {
        $this->registerArgument('cssLibs', 'array', 'Array of css libs');
        $this->registerArgument('includeJSFooterlibs', 'array', 'Array of custom JavaScript file to be loaded');
        $this->registerArgument('includeJSFooter', 'array', 'Array of custom JavaScript file to be loaded');
        $this->registerArgument('inlineLanguageLabelFiles', 'array', 'Array of labels files');
    }

    /**
     * Register configured assets with the page renderer.
     */
    public function render(): string
    {
        $includeJSFooterlibs = $this->arguments['includeJSFooterlibs'] ?? [];
        $includeJSFooter = $this->arguments['includeJSFooter'] ?? [];
        $cssLibs = $this->arguments['cssLibs'] ?? [];
        $inlineLanguageLabelFiles = $this->arguments['inlineLanguageLabelFiles'] ?? [];
        $includeJSFooterlibs = is_array($includeJSFooterlibs) ? $includeJSFooterlibs : [$includeJSFooterlibs];
        $includeJSFooter = is_array($includeJSFooter) ? $includeJSFooter : [$includeJSFooter];
        $cssLibs = is_array($cssLibs) ? $cssLibs : [$cssLibs];
        $inlineLanguageLabelFiles = is_array($inlineLanguageLabelFiles) ? $inlineLanguageLabelFiles : [$inlineLanguageLabelFiles];

        $pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);

        foreach ($includeJSFooterlibs as $footerlib) {
            $pageRenderer->addJsFooterLibrary($footerlib, $footerlib, '', true, false, '', true);
        }
        foreach ($includeJSFooter as $footerlib) {
            $pageRenderer->addJsFooterFile($footerlib, '', true, false, '', true);
        }
        foreach ($cssLibs as $cssLib) {
            $pageRenderer->addCssLibrary($cssLib, 'stylesheet', 'all', '', true, false, '', true);
        }
        foreach ($inlineLanguageLabelFiles as $inlineLanguageLabelFile) {
            $pageRenderer->addInlineLanguageLabelFile($inlineLanguageLabelFile, 'js.');
        }

        return '';
    }
}
