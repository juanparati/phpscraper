<?php
return [
    'recipe'   =>
    [
        'description'   => 'Scraper recipe',
        'optional'      => false,
    ],
    'url' =>
    [
        'description'   => 'URL to scrape',
        'optional'      => true
    ],
    'file' =>
    [
        'description'   => 'File to save',
        'optional'      => true,
        'long_arg'      => 'file',
    ],
    'max-req-sec' =>
    [
        'long_arg'      => 'max-req-sec',
        'description'   => 'Maximum requests per second',
        'optional'      => true,
        'value'         => 0
    ],
    'timeout'     =>
    [
        'long_arg'      => 'timeout',
        'description'   => 'Maximum request timeout (milliseconds)',
        'optional'      => true,
        'value'         => 3000
    ],
    'user-agent'        =>
    [
        'long_arg'      => 'user-agent',
        'description'   => 'User agent string',
        'optional'      => true,
        'value'         => 'Mozilla/5.0 (compatible; PHPScraper/1.1; +http://github.com/juanparati/phpscraper)'
    ],
    'max-redirects'     =>
    [
        'long_arg'      => 'max-redirections',
        'description'   => 'Maximum number of redirections',
        'optional'      => true,
        'value'         => 3
    ],
    'help'     =>
    [
        'long_arg'      => 'help',
        'short_arg'     => 'h',
        'description'   => 'Show help',
        'optional'      => true,
    ]
];
