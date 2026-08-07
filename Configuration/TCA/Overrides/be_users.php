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


// Extend user settings
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addUserSetting(
    'disableQuickeditInPageModule',
    [
        'label' => 'LLL:EXT:quickedit/Resources/Private/Language/Backend.xlf:usersettings.disableQuickeditInPageModule',
        'config' => [
            'type' => 'check',
            'renderType' => 'checkboxToggle',
        ]
    ]
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

$GLOBALS['TCA']['be_users']['columns']['user_settings']['showitem'] .= ',
            --div--;LLL:EXT:quickedit/Resources/Private/Language/Backend.xlf:usersettings.quickeditTab,disableQuickeditInPageModule,quickeditDefaultHidden';
