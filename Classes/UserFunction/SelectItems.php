<?php
namespace Cobweb\Contentwidgets\UserFunction;

/**
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\TypoScript\ExtendedTemplateService;
use TYPO3\CMS\Core\Utility\RootlineUtility;

/**
 * TCA extension for contentwidgets extension.
 *
 * @author Roberto Presedo <typo3@cobweb.ch>
 * @author Francois Suter <typo3@cobweb.ch>
 * @package TYPO3
 * @subpackage tx_contentwidgets
 */
class SelectItems
{

    /**
     * Returns the list of "lib" items in the Typoscript configuration.
     *
     * @param array $parameters Field configuration
     * @return array
     */
    function selectTypoScriptLibElements($parameters)
    {
        // Get the rootline of the current page (i.e. the page the element being edited is on)
        $rootLineUtility = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
                RootlineUtility::class,
                $currentPage = $parameters['row']['pid']
        );
        // Parse TypoScript template for the rootline
        $templateService = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(ExtendedTemplateService::class);
        $templateService->tt_track = false;
        $templateService->init();
        $templateService->runThroughTemplates(
                $rootLineUtility->get()
        );
        $templateService->generateConfig();
        // Get all the TypoScript objects in the top-level "lib" object
        $typoScriptObjects = array();
        foreach ($templateService->setup['lib.'] as $key => $code) {
            // Keep only the TS configuration of the object itself, not the declaration of its key
            if (substr($key, -1) !== '.' && is_array($templateService->setup['lib.'][$key . '.'])) {
                $typoScriptObjects[] = $key;
            }
        }
        // Sort the TypoScript objects and add to the select items
        sort($typoScriptObjects);
        foreach ($typoScriptObjects as $objectName) {
            $parameters['items'][] = array(
                    $objectName,
                    $objectName
            );
        }
    }
}
