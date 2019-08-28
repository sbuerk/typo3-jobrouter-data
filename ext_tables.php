<?php
defined('TYPO3_MODE') || die('Access denied.');

(function ($extensionKey) {
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

    $GLOBALS['TYPO3_CONF_VARS']['LOG']['Brotkrueml']['JobRouterData']['writerConfiguration'] = [
        \TYPO3\CMS\Core\Log\LogLevel::WARNING => [
            \TYPO3\CMS\Core\Log\Writer\FileWriter::class => [
                'logFile' => \TYPO3\CMS\Core\Core\Environment::getVarPath() . '/log/' . $extensionKey . '.log'
            ],
        ],
    ];
})('jobrouter_data');
