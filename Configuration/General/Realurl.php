<?php
// Load RealURL configuration, if needed
if (!isset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['realurl']['_DEFAULT']['fileName']['index']['widget.html'])) {
	$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['realurl']['_DEFAULT']['fileName']['index']['widget.html'] = array(
		'keyValues' => array(
			'type' => 4653
		)
	);
	$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['realurl']['_DEFAULT']['postVarSets']['_DEFAULT']['cwt'][]['GETvar'] = 'cw_ctype';
	$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['realurl']['_DEFAULT']['postVarSets']['_DEFAULT']['cwv'][]['GETvar'] = 'cw_cvar';
	$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['realurl']['_DEFAULT']['postVarSets']['_DEFAULT']['cwc'][]['GETvar'] = 'cw_cuid';
	$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['realurl']['_DEFAULT']['postVarSets']['_DEFAULT']['cwb'][]['GETvar'] = 'cw_cback';
	$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['realurl']['_DEFAULT']['postVarSets']['_DEFAULT']['cwo'][]['GETvar'] = 'cw_output';
}
