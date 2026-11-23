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

$quickeditTab = '--div--;LLL:EXT:quickedit/Resources/Private/Language/Backend.xlf:usersettings.quickeditTab';

// Own "Quickedit" tab in the be_users record, otherwise the field ends up in the last Core tab ("Extended")
TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('be_users', $additionalFields);
TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
    'be_users',
    $quickeditTab . ', quickedit_disableToolbar'
);

unset($additionalFields);


// Extend user settings: own "Quickedit" tab containing the quickedit settings

$GLOBALS['TCA']['be_users']['columns']['user_settings']['showitem'] = trim(
    ($GLOBALS['TCA']['be_users']['columns']['user_settings']['showitem'] ?? '') . ', ' . $quickeditTab,
    ", \t\n\r"
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addUserSetting(
    'disableQuickeditInPageModule',
    [
        'label' => 'LLL:EXT:quickedit/Resources/Private/Language/Backend.xlf:usersettings.disableQuickeditInPageModule',
        'config' => [
            'type' => 'check',
            'renderType' => 'checkboxToggle',
        ]
    ],
    'after:' . $quickeditTab
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addUserSetting(
    'quickeditDefaultHidden',
    [
        'label' => 'LLL:EXT:quickedit/Resources/Private/Language/Backend.xlf:usersettings.quickeditDefaultHidden',
        'config' => [
            'type' => 'check',
            'renderType' => 'checkboxToggle',

        ]
    ],
    'after:disableQuickeditInPageModule'
);

unset($quickeditTab);
