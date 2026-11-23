<?php
$EM_CONF[$_EXTKEY] = [
    'title'            => 'Toolbar for editing page properties',
    'description'      => 'This extension provides a configurable toolbar for editing page properties.',
    'version'          => '4.0.0',
    'category'         => 'be',
    'constraints'      => [
        'depends'   => [
            'typo3' => '14.3.0 - 14.3.99',
            'backend' => '14.3.0 - 14.3.99',
            'fluid' => '14.3.0 - 14.3.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
    'state'            => 'beta',
    'author'           => 'Alexander Böhm',
    'author_email'     => 't3extensions@punkt.de',
    'author_company'   => 'punkt.de GmbH',
];
