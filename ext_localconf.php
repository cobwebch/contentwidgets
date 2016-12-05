<?php
if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}

// Include RealURL configuration
$extensionPath = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY);
require_once($extensionPath . 'Configuration/General/Realurl.php');

// The first plugin is a full-blown content element
// It outputs the widget itself
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPItoST43(
        $_EXTKEY,
        'Classes/Controller/WidgetController.php',
        '_pi1',
        'CType',
        1
);
// The second plugin is never placed directly as content element
// It performs the rendering according to the parameters prepared by the pi1 plugin
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPItoST43(
        $_EXTKEY,
        'Classes/Controller/OutputController.php',
        '_pi2',
        'CType',
        1
);

// Add TSconfig for new content element wizard
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig('<INCLUDE_TYPOSCRIPT: source="FILE:EXT:contentwidgets/Configuration/TSconfig/ContentElementWizard.ts">');
