<?php
defined('TYPO3_MODE') || die('Access denied.');

(function ($extensionKey = 'jobrouter_data') {
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
        'Brotkrueml.JobRouterData',
        'jobrouter',
        'jobrouterdata',
        '',
        [
            'Backend' => 'list',
        ],
        [
            'access' => 'admin',
            'icon' => 'EXT:' . $extensionKey . '/Resources/Public/Icons/jobrouter-data-module.svg',
            'labels' => 'LLL:EXT:' . $extensionKey . '/Resources/Private/Language/BackendModule.xlf',
        ]
    );

    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
        'Brotkrueml.JobRouterData',
        'Pi',
        'LLL:EXT:' . $extensionKey . '/Resources/Private/Language/Plugin.xlf:plugin_title',
        'EXT:' . $extensionKey . '/Resources/Public/Icons/plugin-jobdata-table.svg'
    );

    $iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
        \TYPO3\CMS\Core\Imaging\IconRegistry::class
    );
    $iconRegistry->registerIcon(
        'jobrouterdata_pi',
        \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
        ['source' => 'EXT:' . $extensionKey . '/Resources/Public/Icons/plugin-jobdata-table.svg']
    );

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(
        '<INCLUDE_TYPOSCRIPT: source="FILE:EXT:' . $extensionKey . '/Configuration/TsConfig/Page/NewContentElementWizard.tsconfig">'
    );

    $GLOBALS['TYPO3_CONF_VARS']['LOG']['Brotkrueml']['JobRouterData']['writerConfiguration'] = [
        \TYPO3\CMS\Core\Log\LogLevel::WARNING => [
            \TYPO3\CMS\Core\Log\Writer\FileWriter::class => [
                'logFile' => \TYPO3\CMS\Core\Core\Environment::getVarPath() . '/log/' . $extensionKey . '.log'
            ],
        ],
    ];

    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['list_type_Info']['jobrouterdata_pi'][$extensionKey] =
        \Brotkrueml\JobRouterData\Hooks\PageLayoutView::class . '->getExtensionSummary';

    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processCmdmapClass'][] =
        \Brotkrueml\JobRouterData\Hooks\TableUpdateHook::class;
})();
