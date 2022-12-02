<?php
$EM_CONF[$_EXTKEY] = [
    'title'            => 'Toolbar for editing page properties',
    'description'      => 'This extension provides a configurable toolbar for editing page properties.',
    'version'          => '1.1.0',
    'category'         => 'be',
    'constraints'      => [
        'depends'   => [
            'typo3' => '11.5.0 - 11.5.99'
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
    'state'            => 'beta',
    'uploadfolder'     => 0,
    'createDirs'       => '',
    'clearCacheOnLoad' => 0,
    'author'           => 'Alexander Böhm',
    'author_email'     => 't3extensions@punkt.de',
    'author_company'   => 'punkt.de GmbH',
    'autoload'         => [
        'psr-4' => [
            'PunktDe\\Quickedit\\' => 'Classes'
        ]
    ]
];
