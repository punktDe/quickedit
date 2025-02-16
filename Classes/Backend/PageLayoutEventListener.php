<?php

declare(strict_types=1);

namespace PunktDe\Quickedit\Backend;

/**
 * (c) 2023 https://punkt.de GmbH - Karlsruhe, Germany - https://punkt.de
 * All rights reserved.
 */

use TYPO3\CMS\Backend\Controller\Event\ModifyPageLayoutContentEvent;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Type\Bitmask\Permission;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\View\ViewFactoryData;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Fluid\View\FluidViewFactory;

class PageLayoutEventListener
{
    /**
     * @var BackendUserAuthentication
     */
    protected BackendUserAuthentication $backendUser;

    /**
     * @var int
     */
    protected int $pageUid;

    /**
     * @var ?array
     */
    protected ?array $pageRecord;

    /**
     * @var int
     */
    protected int $language;

    /**
     * @var int
     */
    protected int $function;



    /**
     * Inject an instance of class TYPO3\CMS\Core\View\ViewFactoryInterface
     *
     * @param FluidViewFactory $viewFactory
     */
    public function __construct(
        private readonly FluidViewFactory $viewFactory,
    ) {}



    /**
     * @param ModifyPageLayoutContentEvent $event
     * @return void
     */
    public function __invoke(ModifyPageLayoutContentEvent $event): void
    {
        $this->backendUser = $GLOBALS['BE_USER'];
        $this->pageUid = (int)($event->getRequest()->getQueryParams()['id'] ?? 0);
        $this->function = (int)($event->getRequest()->getQueryParams()['function'] ?? 0);
        $this->pageRecord = BackendUtility::getRecord('pages', $this->pageUid);
        $this->language = (int)BackendUtility::getModuleData(['language'], [], 'web_layout')['language'];

        if ($this->pageUid > 0 && is_array($this->pageRecord)) {
            $this->updatePageRecordIfOverlay();
        }

        $event->addHeaderContent($this->renderToolbar());
    }



    /**
     * @return void
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
    public function renderToolbar(): string
    {
        if (!$this->toolbarIsEnabledForUser()) {
            return '';
        }

        /** @var PageRenderer $pageRenderer */
        $pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
        $pageRenderer->loadJavaScriptModule('@punktde/quickedit/quickedit.js');

        $viewFactoryData = new ViewFactoryData(
            templateRootPaths: ['EXT:quickedit/Resources/Private/Templates/Backend'],
            partialRootPaths: ['EXT:quickedit/Resources/Private/Partials/Backend'],
            templatePathAndFilename: 'EXT:quickedit/Resources/Private/Templates/Backend/Quickedit.html'
        );

        $view = $this->viewFactory->create($viewFactoryData);

        $view->assign('pageId', $this->pageRecord['uid']);
        $view->assign('originPageId', $this->pageRecord['l10n_parent'] ?: $this->pageRecord['uid']);
        $view->assign('languageId', $this->language);
        $view->assign('functionId', $this->function);
        $view->assign('config', $this->getFieldConfigForPage());
        $view->assign('isVisible', $this->isVisible());

        return $view->render();
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
     * @return array
     */
    protected function getFieldConfigForPage(): array
    {
        $configForPageType = $this->getConfigForCurrentPage();

        if (empty($configForPageType) === false) {
            foreach ($configForPageType as $key => &$singleConfig) {
                $singleConfig['fields'] = $this->prepareFieldsList($singleConfig['fields']);

                if ($singleConfig['fields'] === '') {
                    unset($configForPageType[$key]);
                    continue;
                }

                if (str_starts_with($singleConfig['label'], 'LLL')) {
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

                    if (str_starts_with($itemLabel, 'LLL')) {
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
            $isVisible = !$this->backendUser->uc['quickeditDefaultHidden'];
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
