<?php

return [
    /*
     * Maximum file size in kilobytes (25 MB = 25600 KB by default)
     */
    'max_file_size' => env('MAX_FILE_SIZE_KB', 25600),

    /*
     * Maximum total upload size in kilobytes (100 MB = 102400 KB by default)
     */
    'max_total_size' => env('MAX_TOTAL_UPLOAD_SIZE_KB', 102400),

    /*
     * Maximum number of attachments per message
     */
    'max_attachments_per_message' => env('MAX_ATTACHMENTS_PER_MESSAGE', 10),

    /*
     * Maximum text message length in characters
     */
    'max_message_length' => env('MAX_MESSAGE_LENGTH', 2000),

    /*
     * Maximum image dimensions (width, height in pixels)
     */
    'max_image_width' => env('MAX_IMAGE_WIDTH', 4096),
    'max_image_height' => env('MAX_IMAGE_HEIGHT', 4096),

    /*
     * Blocked dangerous executable extensions (server script execution safety)
     */
    'blocked_extensions' => [
        'php', 'phtml', 'php3', 'php4', 'php5', 'phps', 'phar',
        'cgi', 'pl', 'asp', 'aspx', 'jsp', 'sh', 'bash', 'zsh', 'cmd', 'ps1',
    ],
];
