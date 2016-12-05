<?php

// Add new columns to tt_content
$newColumns = array(
        'tx_contentwidgets_contenttype' => array(
                'exclude' => 1,
                'label' => 'LLL:EXT:contentwidgets/Resources/Private/Language/locallang_db.xlf:tt_content.content_type',
                'config' => array(
                        'default' => 'record',
                        'type' => 'select',
                        'items' => array(
                                array('LLL:EXT:contentwidgets/Resources/Private/Language/locallang_db.xlf:tt_content.content_type.record', 'record'),
                                array('LLL:EXT:contentwidgets/Resources/Private/Language/locallang_db.xlf:tt_content.content_type.lib', 'lib')
                        ),
                        'size' => 1,
                        'minitems' => 1,
                        'maxitems' => 1,
                )
        ),
        'tx_contentwidgets_recordelements' => array(
                'displayCond' => 'FIELD:tx_contentwidgets_contenttype:=:record',
                'exclude' => 1,
                'label' => 'LLL:EXT:contentwidgets/Resources/Private/Language/locallang_db.xlf:tt_content.content_elements',
                'config' => array(
                        'type' => 'group',
                        'internal_type' => 'db',
                        'allowed' => 'tt_content',
                        'size' => 5,
                        'minitems' => 1,
                        'maxitems' => 15,
                        'show_thumbs' => 0,
                        'wizards' => array(
                                'suggest' => array(
                                        'type' => 'suggest',
                                        'default' => array(
                                                'searchWholePhrase' => 1,
                                        ),
                                ),
                        ),
                )
        ),
        'tx_contentwidgets_libelement' => array(
                'displayCond' => 'FIELD:tx_contentwidgets_contenttype:=:lib',
                'exclude' => 0,
                'label' => 'LLL:EXT:contentwidgets/Resources/Private/Language/locallang_db.xlf:tt_content.lib_element_name ',
                'config' => array(
                        'type' => 'select',
                        'size' => 1,
                        'minitems' => 0,
                        'maxitems' => 1,
                        'itemsProcFunc' => \Cobweb\Contentwidgets\UserFunction\SelectItems::class . '->selectTypoScriptLibElements',
                )
        ),
        'tx_contentwidgets_loadinglabel' => array(
                'exclude' => 0,
                'label' => 'LLL:EXT:contentwidgets/Resources/Private/Language/locallang_db.xlf:tt_content.loadingLabel ',
                'config' => array(
                        'type' => 'input',
                        'size' => '32',
                )
        ),
);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns(
        'tt_content',
        $newColumns
);

// Define showitem property for the pi1 plug-in
$showItem = '--palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.general;general, --palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.header;header,';
$showItem .= ', --div--;LLL:EXT:contentwidgets/Resources/Private/Language/locallang_db.xlf:tabs.widget_properties, tx_contentwidgets_contenttype,  tx_contentwidgets_recordelements, tx_contentwidgets_libelement, tx_contentwidgets_loadinglabel,';
$showItem .= '--div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.appearance, --palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.frames;frames, --palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.textlayout;textlayout,';
$showItem .= '--div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.access, --palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.visibility;visibility, --palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.access;access,';
$showItem .= '--div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.extended';
$GLOBALS['TCA']['tt_content']['types']['contentwidgets_pi1']['showitem'] = $showItem;

// Add icon for new type
$GLOBALS['TCA']['tt_content']['ctrl']['typeicon_classes']['contentwidgets_pi1'] = 'tx_contentwidgets-content-element';

// Add widget type to request update fields
(isset($GLOBALS['TCA']['tt_content']['ctrl']['requestUpdate']))
        ? $GLOBALS['TCA']['tt_content']['ctrl']['requestUpdate'] .= ',tx_contentwidgets_contenttype'
        : $GLOBALS['TCA']['tt_content']['ctrl']['requestUpdate'] = 'tx_contentwidgets_contenttype';
// Tune plugin-related fields
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist']['contentwidgets_pi1'] = 'layout,select_key,pages';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['contentwidgets_pi1'] = 'tt_content';


// Add "pi1" plugin
// NOTE: it is intentional that "pi2" is not added here. It is not supposed to be instantiated as a simple content element, only per TypoScript.
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPlugin(
        array(
                'LLL:EXT:contentwidgets/Resources/Private/Language/locallang.xlf:plugin_wizard_title',
                'contentwidgets_pi1',
                'tx_contentwidgets-content-element'
        ),
        'CType',
        'contentwidgets'
);
