<?php

declare(strict_types=1);

namespace PunktDe\Quickedit\Backend;

/**
 * (c) 2022 https://punkt.de GmbH - Karlsruhe, Germany - https://punkt.de
 * All rights reserved.
 */

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Type\Bitmask\Permission;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;


class PageLayoutHeader
{
    /**
     * @var BackendUserAuthentication
     */
    protected $backendUser;

    /**
     * @var int
     */
    protected $pageUid;

    /**
     * @var array
     */
    protected $pageRecord;

    /**
     * @var int
     */
    protected $language;



    public function __construct()
    {
        $this->backendUser = $GLOBALS['BE_USER'];
        $this->pageUid = (int)GeneralUtility::_GET('id');
        $this->pageRecord = BackendUtility::getRecord('pages', $this->pageUid);
        $this->language = (int)BackendUtility::getModuleData(['language'], [], 'web_layout')['language'];

        $this->updatePageRecordIfOverlay();
    }



    /**
     *
     */
    protected function updatePageRecordIfOverlay(): void
    {
        if ($this->language > 0) {
            $overlayRecords = BackendUtility::getRecordLocalization(
                'pages',
                $this->pageUid,
                $this->language
            );

            if (is_array($overlayRecords) && array_key_exists(0, $overlayRecords) && is_array($overlayRecords[0])) {
                $this->pageRecord = $overlayRecords[0];
            }
        }
    }



    /**
     * @return string
     */
    public function render(): string
    {
        if (!$this->toolbarIsEnabledForUser()) {
            return '';
        }

        $pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
        /** @var PageRenderer $pageRenderer */
        $pageRenderer->loadRequireJsModule('TYPO3/CMS/Quickedit/Quickedit');

        $standaloneView = $this->initializeStandaloneView();
        $standaloneView->assign('pageId', $this->pageRecord['uid']);
        $standaloneView->assign('config', $this->getFieldConfigForPage());
        $standaloneView->assign('isVisible', $this->isVisible());

        return $standaloneView->render();
    }



    /**
     * Checks if the toolbar is enabled or disabled.
     * Method checks current user setting, access rights to current page and pages in general,
     * user record and group records.
     *
     * @return bool
     */
    protected function toolbarIsEnabledForUser(): bool
    {
        $isEnabled = true;

        if (
            array_key_exists('disableQuickeditInPageModule', $this->backendUser->uc) &&
            (bool)$this->backendUser->uc['disableQuickeditInPageModule'] === true
        ) {
            $isEnabled = false;
        }

        if (!$this->backendUser->doesUserHaveAccess($this->pageRecord, Permission::PAGE_EDIT) ||
            !$this->backendUser->check('tables_modify', 'pages')) {
            $isEnabled = false;
        }

        if (
            array_key_exists('quickedit_disableToolbar', $this->backendUser->user) &&
            $this->backendUser->user['quickedit_disableToolbar']
        ) {
            $isEnabled = false;
        }

        foreach ($this->backendUser->userGroups as $group) {
            if (
                array_key_exists('quickedit_disableToolbar', $group) &&
                $group['quickedit_disableToolbar']
            ) {
                $isEnabled = false;
            }
        }

        return $isEnabled;
    }



    /**
     * Initializes the view by setting template and partial paths
     *
     * @return StandaloneView
     */
    protected function initializeStandaloneView(): StandaloneView
    {
        /** @var StandaloneView $standaloneView */
        $standaloneView = GeneralUtility::makeInstance(StandaloneView::class);
        $templatesPath = GeneralUtility::getFileAbsFileName('EXT:quickedit/Resources/Private/Templates/Backend');
        $templateFileName = 'Quickedit.html';

        $standaloneView->setTemplateRootPaths(
            array($templatesPath)
        );
        $standaloneView->setPartialRootPaths(
            array(GeneralUtility::getFileAbsFileName('EXT:quickedit/Resources/Private/Partials/Backend'))
        );

        $standaloneView->setTemplatePathAndFilename($templatesPath . '/' . $templateFileName);

        return $standaloneView;
    }



    /**
     * @return array
     */
    protected function getFieldConfigForPage(): array
    {
        $configForPageType = $this->getConfigForCurrentPage();

        if (count($configForPageType) > 0) {
            foreach ($configForPageType as $key => &$singleConfig) {
                $singleConfig['fields'] = $this->prepareFieldsList($singleConfig['fields']);

                if ($singleConfig['fields'] === '') {
                    unset($configForPageType[$key]);
                    continue;
                }

                if (strpos($singleConfig['label'], 'LLL') === 0) {
                    $singleConfig['label'] = LocalizationUtility::translate($singleConfig['label']);
                }

                $this->processPreviewFields($singleConfig);
            }

            return $configForPageType;
        }

        return [];
    }



    /**
     * Get the Quickedit config for current doktype, sort groups by their number in config.
     *
     * @return array
     */
    protected function getConfigForCurrentPage(): array
    {
        $pageTsConfig = BackendUtility::getPagesTSconfig($this->pageUid);
        $configForPageType = [];

        if (
            array_key_exists('mod.', $pageTsConfig) &&
            is_array($pageTsConfig['mod.']) &&
            array_key_exists('web_layout.', $pageTsConfig['mod.']) &&
            is_array($pageTsConfig['mod.']['web_layout.']) &&
            array_key_exists('PageTypes.', $pageTsConfig['mod.']['web_layout.'])
        ) {
            $quickeditConfig = $pageTsConfig['mod.']['web_layout.']['PageTypes.'];

            if (is_array($quickeditConfig) && array_key_exists($this->pageRecord['doktype'] . '.', $quickeditConfig)) {
                $configForPageType = $quickeditConfig[$this->pageRecord['doktype'] . '.']['config.'];
                ksort($configForPageType);
            }
        }

        return $configForPageType;
    }



    /**
     * Prepares list of configured fields, trims field names and checks access rights of backend user.
     * Returns a cleaned field list.
     *
     * @param $fields string
     * @return string
     */
    protected function prepareFieldsList(string $fields): string
    {
        $fieldsArray = [];

        if ($fields !== '') {
            $fieldsArray = explode(',', $fields);
            $fieldsArray = array_map('trim', $fieldsArray);

            foreach ($fieldsArray as $index => $field) {
                if ($this->isFieldDefined($field) === false) {
                    unset($fieldsArray[$index]);
                    continue;
                }

                if ($this->userHasAccessToField($field) === false
                    || $this->fieldIsAvailableForLanguage($field) === false) {
                    unset($fieldsArray[$index]);
                }
            }
        }

        return implode(',', $fieldsArray);
    }



    /**
     * @param $field string
     * @return bool
     */
    protected function userHasAccessToField(string $field): bool
    {
        return $field !== '' && (!array_key_exists('exclude', $GLOBALS['TCA']['pages']['columns'][$field]) ||
                $GLOBALS['TCA']['pages']['columns'][$field]['exclude'] === 0 ||
                $this->backendUser->check('non_exclude_fields', 'pages:' . $field));
    }



    /**
     * @param string $field
     * @return bool
     */
    protected function fieldIsAvailableForLanguage(string $field): bool
    {
        if ($this->language > 0) {
            return $field !== '' && (
                    !array_key_exists('l10n_mode', $GLOBALS['TCA']['pages']['columns'][$field]) ||
                    $GLOBALS['TCA']['pages']['columns'][$field]['l10n_mode'] !== 'exclude'
                );
        }

        return true;
    }



    /**
     * Checks set previewFields and get the corresponding field labels and values for display in backend.
     *
     * @param $groupConfig array
     */
    protected function processPreviewFields(array &$groupConfig): void
    {
        if (array_key_exists('previewFields', $groupConfig)) {
            $groupConfig['fieldValues'] = [];

            if ($groupConfig['previewFields'] === '*') {
                $groupConfig['previewFields'] = $groupConfig['fields'];
            } else {
                $groupConfig['previewFields'] = $this->prepareFieldsList($groupConfig['previewFields']);
            }

            $previewFieldsArray = explode(',', $groupConfig['previewFields']);

            foreach ($previewFieldsArray as $field) {
                if ($field !== '') {
                    $groupConfig['fieldValues'][$field]['value'] = BackendUtility::getProcessedValue(
                        'pages',
                        $field,
                        $this->pageRecord[$field],
                        0,
                        false,
                        false,
                        $this->pageUid
                    );

                    $itemLabel = BackendUtility::getItemLabel('pages', $field);

                    if (strpos($itemLabel, 'LLL') === 0) {
                        $itemLabel = LocalizationUtility::translate($itemLabel);
                    }

                    $groupConfig['fieldValues'][$field]['label'] = $itemLabel;
                }
            }
        }
    }



    /**
     * Checks if user has set the toolbar to hidden by default in his user settings.
     *
     * If a user opens the toolbar the current status is saved and overrides the
     * default visibility of the toolbar for only that page!
     *
     * @return bool
     */
    protected function isVisible(): bool
    {
        $isVisible = true;

        if (array_key_exists('quickeditDefaultHidden', $this->backendUser->uc)) {
            $isVisible = !(bool)$this->backendUser->uc['quickeditDefaultHidden'];
        }

        if (array_key_exists('quickedit', $this->backendUser->uc) &&
            array_key_exists('visible', $this->backendUser->uc['quickedit']) &&
            array_key_exists($this->pageRecord['uid'], $this->backendUser->uc['quickedit']['visible'])) {
            $isVisible = (bool)$this->backendUser->uc['quickedit']['visible'][$this->pageRecord['uid']];
        }

        return $isVisible;
    }



    /**
     * @param string $field
     * @return bool
     */
    protected function isFieldDefined(string $field): bool
    {
        return array_key_exists($field, $GLOBALS['TCA']['pages']['columns']);
    }
}
