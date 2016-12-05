<?php
if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}

// Register icon for new content type
/** @var \TYPO3\CMS\Core\Imaging\IconRegistry $iconRegistry */
$iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);
$iconRegistry->registerIcon(
        'tx_contentwidgets-content-element',
        \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
        [
            'source' => 'EXT:contentwidgets/Resources/Public/Images/ExtensionIcon.svg'
        ]
);
