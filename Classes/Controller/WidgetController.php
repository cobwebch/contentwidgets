<?php
namespace Cobweb\Contentwidgets\Controller;

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

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Plugin\AbstractPlugin;

/**
 * Builds JS calls to output page content or Typoscript libraries content using AJAX.
 *
 * @author Roberto Presedo <typo3@cobweb.ch>
 * @package TYPO3
 * @subpackage tx_contentwidgets
 */
class WidgetController extends AbstractPlugin
{
    /**
     * Default page rendering page type. Can be changed in the extension configuration.
     */
    const RENDER_TYPE = 4653;

    public $prefixId = 'tx_contentwidgets_pi1'; // Same as class name
    public $scriptRelPath = 'pi1/class.tx_contentwidgets_pi1.php'; // Path to this script relative to the extension dir.
    public $extKey = 'contentwidgets'; // The extension key.
    public $pi_checkCHash = true;

    /**
     * @var array Plugin configuration
     */
    public $conf = array();

    /**
     * @var int Type number of the page type rendering
     */
    protected $renderType;

    /**
     * Returns the plugin content.
     *
     * @param string $content Already existing content
     * @param array $configuration TypoScript configuration for this plugin (tx_contentwidgets_pi1)
     * @return string The content to display on the website
     */
    public function main($content, $configuration)
    {
        // Initialisation of configuration
        $this->pi_setPiVarDefaults();
        $this->pi_loadLL('EXT:contentwidgets/Resources/Private/Language/locallang_plugin.xlf');
        $this->init($configuration);
        // Render only if type is defined
        $return = '';
        if ($this->conf['cw_type']) {
            // Get all GET and POST values into one array
            $gp = array_merge(
                    GeneralUtility::_POST(),
                    GeneralUtility::_GET()
            );
            unset($gp['cHash']);
            // Unset elements that may interfere with the login process
            unset($gp['user'], $gp['pass'], $gp['challenge'], $gp['logintype']);

            // Build the url to the current page, with current GET and POST values
            $localCObj = GeneralUtility::makeInstance(ContentObjectRenderer::class);
            $typolinkConfiguration = array();
            $typolinkConfiguration['parameter'] = (int)$configuration['sourcePid'] === 0 ? $GLOBALS['TSFE']->id : (int)$config['sourcePid'];
            $typolinkConfiguration['useCacheHash'] = 0;
            $typolinkConfiguration['returnLast'] = 'url';
            $typolinkConfiguration['additionalParams'] = '&' . http_build_query($gp);
            $typolink = $localCObj->typoLink(
                    '',
                    $typolinkConfiguration
            );

            // Define the loading message
            if ($this->conf['loadingLabel']) {
                $loadingLabel = $this->conf['loadingLabel'];
            } else {
                $loadingLabel = $this->pi_getLL('loadingLabel');
            }
            // Build the output
            $url = $typolink;
            $search = array('###ID###', '###LABEL###');
            foreach ($this->conf['cw_elements'] as $element) {
                $currentDivId = 'cwidgets_' . $this->cObj->data['uid'] . '_' . $element;

                // For the first item, add the loading mask
                if ($return === '') {
                    $replacements = array($currentDivId, $loadingLabel);
                    $return .= str_replace($search, $replacements, $this->conf['loadingMask']);
                } else {
                    $return .= '<div id="' . $currentDivId . '""></div>';
                }
                $return .= $this->buildScriptTag(
                        $url,
                        $this->conf['cw_type'],
                        $element,
                        $currentDivId
                );
            }
        }
        return $return;
    }

    /**
     * Builds the JS call to cwidgets function to generate the AJAX to the desired content.
     *
     * @param string $url Url of the current page
     * @param string $ctype Type of content to display ("record" or "lib")
     * @param string $cvar Id of the tt_content element or the name of the TypoScript lib to display
     * @param string $target Id of the DOM element after which the content element will be added
     * @return string
     */
    protected function buildScriptTag($url, $ctype, $cvar, $target)
    {
        $urlParts = @parse_url($url);
        $scriptUrl = '';
        if ($urlParts['path']) {
            $scriptUrl .= $urlParts['path'];
        }
        if ($urlParts['query']) {
            $scriptUrl .= '?' . $urlParts['query'];
        }

        return '
            <script type="text/javascript" id="' . $target . '_js">
                //<![CDATA[
				cwidgets("' . $scriptUrl . '","' . $ctype . '","' . $cvar . '","' . $this->renderType . '","' . $target . '");
		        //]]>
            </script>
        ';
    }

    /**
     * Checks the data received and sets the configuration of the plugin.
     *
     * @param array $configuration Base TypoScript configuration
     * @return void
     */
    protected function init($configuration)
    {
        $this->conf = $configuration;

        // Kind of content (tt_content records or Typoscript lib)
        $this->cObj->data['tx_contentwidgets_contenttype'] === 'record' ?
                $this->conf['cw_type'] = 'record' :
                $this->conf['cw_type'] = 'lib';

        if (isset($this->conf['renderType'])) {
            $this->renderType = $this->conf['renderType'];
        } else {
            $this->renderType = self::RENDER_TYPE;
        }
        if ($this->conf['cw_type'] === 'record') {
            $this->conf['cw_elements'] = explode(
                    ',',
                    $this->cObj->data['tx_contentwidgets_recordelements']
            );
        } else {
            $this->conf['cw_elements'] = explode(
                    ',',
                    (string)addslashes($this->cObj->data['tx_contentwidgets_libelement'])
            );
        }
        $this->conf['loadingLabel'] = (string)$this->cObj->data['tx_contentwidgets_loadinglabel'];
    }
}
