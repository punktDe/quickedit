<?php
if (!defined('TYPO3')) {
    die ('Access denied.');
}

$GLOBALS['TYPO3_CONF_VARS']['BE']['stylesheets']['quickedit'] = 'EXT:quickedit/Resources/Public/Backend/Css';


// Extend user settings
$GLOBALS['TYPO3_USER_SETTINGS']['columns']['disableQuickeditInPageModule'] = [
    'label' => 'LLL:EXT:quickedit/Resources/Private/Language/Backend.xlf:usersettings.disableQuickeditInPageModule',
    'type' => 'check'
];
$GLOBALS['TYPO3_USER_SETTINGS']['columns']['quickeditDefaultHidden'] = [
    'label' => 'LLL:EXT:quickedit/Resources/Private/Language/Backend.xlf:usersettings.quickeditDefaultHidden',
    'type' => 'check'
];

$GLOBALS['TYPO3_USER_SETTINGS']['showitem'] .= ',
            --div--;LLL:EXT:quickedit/Resources/Private/Language/Backend.xlf:usersettings.quickeditTab,disableQuickeditInPageModule,quickeditDefaultHidden';
