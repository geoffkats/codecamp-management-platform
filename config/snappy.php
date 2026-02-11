<?php

return [
    'pdf' => [
        'enabled' => true,
        'binary' => env('WKHTMLTOPDF_BINARY', 'C:\\PROGRA~1\\wkhtmltopdf\\bin\\wkhtmltopdf.exe'),
        'timeout' => false,
        'options' => [
            'enable-local-file-access' => true,
        ],
        'env' => [],
    ],
    'image' => [
        'enabled' => true,
        'binary' => env('WKHTMLTOIMAGE_BINARY', 'C:\\PROGRA~1\\wkhtmltopdf\\bin\\wkhtmltoimage.exe'),
        'timeout' => false,
        'options' => [],
        'env' => [],
    ],
];
