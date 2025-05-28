<?php

return [
    'extensions' => [
        // Documents
        'application/pdf' => 'pdf',
        'application/msword' => 'doc',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
        'application/vnd.ms-excel' => 'xls',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
        'application/vnd.ms-powerpoint' => 'ppt',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'pptx',
        'text/plain' => 'txt',
        'text/csv' => 'csv',
        'application/csv' => 'csv',
        'application/vnd.ms-excel' => 'csv', // Sometimes used for CSV
        'application/x-csv' => 'csv',
        'text/comma-separated-values' => 'csv',
        'application/octet-stream' => 'csv', // Occasionally CSV files are detected as this
        'application/json' => 'json',
        'application/zip' => 'zip',
        'application/x-rar-compressed' => 'rar',

        // Images
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
        'image/bmp' => 'bmp',
        'image/svg+xml' => 'svg',
        'image/tiff' => 'tiff',

        // Videos
        'video/mp4' => 'mp4',
        'video/mpeg' => 'mpeg',
        'video/webm' => 'webm',
        'video/x-msvideo' => 'avi',
        'video/quicktime' => 'mov',
        'video/x-flv' => 'flv',

        // Audio
        'audio/mpeg' => 'mp3',
        'audio/wav' => 'wav',
        'audio/ogg' => 'ogg',
        'audio/aac' => 'aac',
        'audio/webm' => 'weba',
    ],
];
