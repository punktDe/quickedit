<?php

if (!defined('TYPO3')) {
    die ('Access denied.');
}


// Register Backend PageLayoutHeader
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/db_layout.php']['drawHeaderHook'][]
    = \PunktDe\Quickedit\Backend\PageLayoutHeader::class . '->render';
