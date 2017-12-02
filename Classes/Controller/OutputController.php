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
use TYPO3\CMS\Frontend\Plugin\AbstractPlugin;

/**
 * Outputs page content or TypoScript libraries content, according to "pi1" selection.
 *
 * @author Roberto Presedo <typo3@cobweb.ch>
 * @package TYPO3
 * @subpackage tx_contentwidgets
 */
class OutputController extends AbstractPlugin
{
    /**
     * Allowed content types
     */
    const ALLOWED_TYPES = array('record', 'lib');
    /**
     * Allowed content types
     */
    const ALLOWED_OUTPUTS = array('local', 'crosssite');

    public $prefixId = 'cwoutput'; // Same as class name
    public $extKey = 'contentwidgets'; // The extension key.
    public $pi_checkCHash = true;

    /**
     * @var array Plugin configuration
     */
    public $conf = array();

    /**
     * @var string HTML to be returned
     */
    protected $return = '';

    /**
     * @var int Type number of the page type rendering
     */
    protected $renderType;

    /**
     * @var string ID of the DOM element in which the generated element will be displayes
     */
    protected $targetId;

    /**
     * @var int|string ID of the content elements or name of the TS object to render
     */
    protected $contentKey;

    /**
     * @var string Type of content to render ("lib" or "record")
     */
    protected $contentType;

    /**
     * @var string Name of the callback function (if any)
     */
    protected $callbackName;

    /**
     * @var string insite or crosssite
     */
    protected $output;

    /**
     * Returns the plugin content.
     *
     * @param string $content Already existing content
     * @param array $configuration Typoscript configuration for this plugin (tx_contentwidgets_pi2)
     * @return string The content to display on the website
     */
    public function main($content, $configuration)
    {
        // Initialisation of configuration
        $this->pi_setPiVarDefaults();
        $this->pi_loadLL('EXT:contentwidgets/Resources/Private/Language/locallang_plugin.xlf');
        $this->init($configuration);

        // Render according to chosen type
        if ($this->contentType === 'record') {
            $recordConfiguration = array(
                    'source' => $this->contentKey,
                    'tables' => 'tt_content'
            );
            try {
                $this->return = $this->cObj->cObjGetSingle('RECORDS', $recordConfiguration);
            } catch (\Exception $e) {
                $message = sprintf(
                        'Content could not be rendered: %s [%d]',
                        $e->getMessage(),
                        $e->getCode()
                );
                die($message);
            }
        } elseif ($this->contentType === 'lib') {
            if (isset($GLOBALS['TSFE']->tmpl->setup[$this->contentType . '.'][$this->contentKey])) {
                $type = $GLOBALS['TSFE']->tmpl->setup[$this->contentType . '.'][$this->contentKey];
                $conf = $GLOBALS['TSFE']->tmpl->setup[$this->contentType . '.'][$this->contentKey . '.'];
                try {
                    $this->return = $this->cObj->cObjGetSingle($type, $conf);
                } catch (\Exception $e) {
                    $message = sprintf(
                            'Content could not be rendered: %s [%d]',
                            $e->getMessage(),
                            $e->getCode()
                    );
                    die($message);
                }
            }
        } else {
            die('Content Type is not valid');
        }

        if ($this->output === 'crosssite'){
            $this->setCrossSiteOutput();
        }

        if ($this->return !== '') {
            $this->wrapOutput();
        }

        return $this->return;
    }


    /**
     * Prepares the content to be returned to a regular js call
     */
    function setCrossSiteOutput()
    {
        $return = '';
        $TempVarName = 'CW_' . substr(md5(uniqid('', 1)), 0, 8);

        // Cleaning the unique var (making sure it's empty)
        $return .= "\n" . 'var ' . $TempVarName . " = '';\n";

        // Cleaning the content to return
        $search = array("\\", "\r", '"', "'", '<');
        $replace = array("\\\\", '', '\"', "\\'", '<"+"');
        $content = str_replace($search, $replace, $this->return);

        // Splitting the content into several lines
        foreach (explode("\n", $content) AS $currentLine) {
            if (trim($currentLine) != '') {
                $return .= "\n" . $TempVarName . ' += "' . trim($currentLine) . '\n";';
            }
        }

        $return .= '
var cw_target = document.getElementById(\'' . $this->targetId . '\');
var newElement = document.createElement("div");
newElement.id = "' . $this->targetId . '_el";
newElement.innerHTML = ' . $TempVarName . ';
cw_target.parentNode.appendChild(newElement, cw_target);
cw_target.parentNode.removeChild(cw_target);';

        $this->return = $return;
    }





    /**
     * Initializes configuration.
     *
     * @param array $configuration Base TypoScript configuration
     * @return void
     */
    protected function init($configuration)
    {
        $this->conf = $configuration;

        $gp = array_merge(
                GeneralUtility::_POST(),
                GeneralUtility::_GET()
        );
        unset($gp['cHash']);
        // Unset elements that may interfere with the login process
        unset($gp['user'], $gp['pass'], $gp['challenge'], $gp['logintype']);

        if (isset($this->conf['renderType'])) {
            $this->renderType = $this->conf['renderType'];
        } else {
            $this->renderType = WidgetController::RENDER_TYPE;
        }

        // Content type must be 'record' or 'lib'
        if (in_array($gp['cw_ctype'], self::ALLOWED_TYPES, true)) {
            $this->contentType = $gp['cw_ctype'];
        } else {
            die('Content Type is not valid');
        }

        if (($gp['cw_cvar'] && $this->contentType === 'lib') || (ctype_digit($gp['cw_cvar']) && $this->contentType === 'record')) {
            $this->contentKey = $gp['cw_cvar'];
        } else {
            die('Content Value is not valid');
        }

        // Optional callback must be alphanumeric
        if (ctype_alnum($gp['cw_cback']) && $gp['cw_cback'] !== '') {
            $this->callbackName = $gp['cw_cback'];
        }
        $this->targetId = $gp['cw_cuid'];

        if (in_array($gp['cw_output'], self::ALLOWED_OUTPUTS, true)) {
            $this->output = $gp['cw_output'];
        }


    }

    /**
     * Prepares the content to be returned to a jQuery ajax call.
     *
     * @return void
     */
    protected function wrapOutput()
    {
        // If there's a callback, add it.
        if ($this->callbackName) {
            $this->return .= "\n";
            $this->return .= '<script type="text/javascript">' . $this->callbackName . '();</script>';
            $this->return .= "\n";
        }
    }

}
