 <?php
defined('TYPO3_MODE') or die();


\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::registerPageTSConfigFile(
    'quickedit',
    'Configuration/TsConfig/Page/DefaultPageTypes.tsconfig',
    'quickedit - Toolbar configuration for default page types');
