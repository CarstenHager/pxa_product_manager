<?php

declare(strict_types=1);

namespace Pixelant\PxaProductManager\UserFunction\TCA;

use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * TCA user function.
 */
class CategoryUserFunction
{
    /**
     * @param $PA
     * @param $fObj
     * @return string
     */
    public function pageModuleLinkField($PA, $fObj): string
    {
        $pages = $PA['row']['pxapm_content_page'] ?? [];
        $page = is_array($pages) ? current($pages) : false;
        if (!is_array($page) || empty($page['uid'])) {
            return '';
        }


        $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
        $uri = (string)$uriBuilder->buildUriFromRoute('web_layout', ['id' => $page['uid']]);

        return sprintf('<a href="%s" class="btn btn-default">%s</a>', htmlspecialchars($uri), htmlspecialchars((string)($page['title'] ?? $page['uid'])));
    }
}
