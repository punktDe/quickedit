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

// Own "Quickedit" tab in the be_groups record, otherwise the field ends up in the last Core tab ("Extended")
TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('be_groups', $additionalFields);
TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
    'be_groups',
    '--div--;LLL:EXT:quickedit/Resources/Private/Language/Backend.xlf:usersettings.quickeditTab, quickedit_disableToolbar'
);

unset($additionalFields);
