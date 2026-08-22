<?php

return [
    'pdf' => [
        'enabled' => true,
        'binary' => env(
            'WKHTMLTOPDF_BINARY',
            PHP_OS_FAMILY === 'Windows'
                ? 'C:\\PROGRA~1\\wkhtmltopdf\\bin\\wkhtmltopdf.exe'
                : '/usr/bin/wkhtmltopdf'
        ),
        'timeout' => false,
        'options' => [
            'enable-local-file-access' => true,
        ],
        'env' => [],
    ],
    'image' => [
        'enabled' => true,
        'binary' => env(
            'WKHTMLTOIMAGE_BINARY',
            PHP_OS_FAMILY === 'Windows'
                ? 'C:\\PROGRA~1\\wkhtmltopdf\\bin\\wkhtmltoimage.exe'
                : '/usr/bin/wkhtmltoimage'
        ),
        'timeout' => false,
        'options' => [],
        'env' => [],
    ],
];
