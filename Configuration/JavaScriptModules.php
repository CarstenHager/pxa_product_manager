<?php
return [
    'dependencies' => ['backend', 'core'],
    // The link handler and form field control can be opened after the initial
    // backend request (for example from an inline record or the link browser).
    'tags' => ['backend.form'],
    'imports' => [
        '@pixelant/pxa-product-manager/' => 'EXT:pxa_product_manager/Resources/Public/JavaScript/',
    ],
];
