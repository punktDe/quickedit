<?php

$additionalFields = [
    'quickedit_disableToolbar' => [
        'label' => 'LLL:EXT:quickedit/Resources/Private/Language/Backend.xlf:setting.disableToolbar',
        'config' => [
            'type' => 'check',
            'items' => [
                [
                    'label' => '',
                ]
            ]
        ]
    ]
];

TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('be_users', $additionalFields);
TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('be_users', 'quickedit_disableToolbar');

unset($additionalFields);
