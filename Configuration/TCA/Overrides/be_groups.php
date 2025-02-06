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

TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('be_groups', $additionalFields);
TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('be_groups', 'quickedit_disableToolbar');

unset($additionalFields);
