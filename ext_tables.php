<?php
defined('TYPO3') || die;

(function () {
    // Register solr hook to handle solr userfunc to detectSerializedValue.
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['solr']['detectSerializedValue']['pxa_product_manager']
        = \Pixelant\PxaProductManager\Hook\Solr\DetectSerializedValue::class;

})();
